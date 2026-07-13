<?php
// process.php

class JiraLogger
{
    private $baseUrl;
    private $email;
    private $apiToken;
    public $accountId;

    public function __construct()
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $this->baseUrl = $env['JIRA_BASE_URL'] ?? '';
            $this->email = $env['JIRA_EMAIL'] ?? '';
            $this->apiToken = $env['JIRA_API_TOKEN'] ?? '';
            $this->accountId = $env['JIRA_ACCOUNT_ID'] ?? '';
        } else {
            die("Error: .env file not found. Please create one from .env.example\n");
        }
    }

    private function request(
        $method,
        $endpoint,
        $data = null
    ) {

        $url =
            $this->baseUrl . $endpoint;

        $ch =
            curl_init($url);

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        curl_setopt(
            $ch,
            CURLOPT_RETURNTRANSFER,
            true
        );

        curl_setopt(
            $ch,
            CURLOPT_CUSTOMREQUEST,
            $method
        );

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $headers
        );

        curl_setopt(
            $ch,
            CURLOPT_USERPWD,
            $this->email .
                ':' .
                $this->apiToken
        );

        if ($data) {

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($data)
            );
        }

        $response =
            curl_exec($ch);

        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        if (curl_errno($ch)) {

            die(curl_error($ch));
        }

        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true),
            'raw' => $response
        ];
    }

    private function htmlToAdf($html)
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $doc->getElementsByTagName('div')->item(0);
        if (!$wrapper) {
            return null;
        }

        $content = $this->convertBlockNodes($wrapper->childNodes);
        if (empty($content)) {
            $content = [
                [
                    'type' => 'paragraph'
                ]
            ];
        }

        return [
            'type' => 'doc',
            'version' => 1,
            'content' => $content
        ];
    }

    private function convertBlockNodes($nodeList)
    {
        $blocks = [];

        foreach ($nodeList as $node) {
            $blocks = array_merge(
                $blocks,
                $this->convertBlockNode($node)
            );
        }

        return $blocks;
    }

    private function convertBlockNode($node)
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = preg_replace('/\s+/', ' ', $node->nodeValue ?? '');
            if (trim($text) === '') {
                return [];
            }

            return [
                $this->buildParagraph([
                    [
                        'type' => 'text',
                        'text' => $text
                    ]
                ])
            ];
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return [];
        }

        $tag = strtolower($node->nodeName);

        if ($tag === 'p') {
            return [
                $this->buildParagraph(
                    $this->convertInlineNodes($node->childNodes)
                )
            ];
        }

        if ($tag === 'br') {
            return [
                [
                    'type' => 'paragraph'
                ]
            ];
        }

        if ($tag === 'ul' || $tag === 'ol') {
            $listItems = [];
            foreach ($node->childNodes as $child) {
                if (
                    $child->nodeType === XML_ELEMENT_NODE
                    && strtolower($child->nodeName) === 'li'
                ) {
                    $itemContent = $this->convertBlockNodes($child->childNodes);
                    if (empty($itemContent)) {
                        $itemContent = [
                            [
                                'type' => 'paragraph'
                            ]
                        ];
                    }

                    $listItems[] = [
                        'type' => 'listItem',
                        'content' => $itemContent
                    ];
                }
            }

            if (empty($listItems)) {
                return [];
            }

            return [
                [
                    'type' => $tag === 'ul' ? 'bulletList' : 'orderedList',
                    'content' => $listItems
                ]
            ];
        }

        if ($tag === 'div') {
            return $this->convertBlockNodes($node->childNodes);
        }

        return [
            $this->buildParagraph(
                $this->convertInlineNodes($node->childNodes)
            )
        ];
    }

    private function convertInlineNodes($nodeList, $marks = [])
    {
        $nodes = [];

        foreach ($nodeList as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = preg_replace('/\s+/', ' ', $node->nodeValue ?? '');
                if (trim($text) === '') {
                    continue;
                }

                $textNode = [
                    'type' => 'text',
                    'text' => $text
                ];

                if (!empty($marks)) {
                    $textNode['marks'] = $marks;
                }

                $nodes[] = $textNode;
                continue;
            }

            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($node->nodeName);

            if ($tag === 'br') {
                $nodes[] = [
                    'type' => 'hardBreak'
                ];
                continue;
            }

            if ($tag === 'strong' || $tag === 'b') {
                $nodes = array_merge(
                    $nodes,
                    $this->convertInlineNodes(
                        $node->childNodes,
                        array_merge($marks, [['type' => 'strong']])
                    )
                );
                continue;
            }

            if ($tag === 'em' || $tag === 'i') {
                $nodes = array_merge(
                    $nodes,
                    $this->convertInlineNodes(
                        $node->childNodes,
                        array_merge($marks, [['type' => 'em']])
                    )
                );
                continue;
            }

            $nodes = array_merge(
                $nodes,
                $this->convertInlineNodes($node->childNodes, $marks)
            );
        }

        return $nodes;
    }

    private function buildParagraph($inlineContent)
    {
        $paragraph = [
            'type' => 'paragraph'
        ];

        if (!empty($inlineContent)) {
            $paragraph['content'] = $inlineContent;
        }

        return $paragraph;
    }

    public function createIssue(
        $projectKey,
        $title,
        $taskCategory,
        $startDate,
        $dueDate,
        $taskSize
    ) {

        $payload = [

            'fields' => [

                'project' => [
                    'key' => $projectKey
                ],

                'summary' => $title,

                'description' => [
                    'type' => 'doc',
                    'version' => 1,
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $title
                                ]
                            ]
                        ]
                    ]
                ],

                'issuetype' => [
                    'name' => 'Task'
                ],

                'priority' => [
                    'id' => '3' // Major
                ],

                'customfield_10800' => $startDate, // Start date

                'customfield_10945' => [
                    'id' => $taskCategory // Task Category
                ],

                'duedate' => $dueDate,

                'customfield_11005' => [
                    'id' => $taskSize // Task Size
                ]
            ]
        ];

        $response =
            $this->request(
                'POST',
                '/rest/api/3/issue',
                $payload
            );

        if (
            $response['status'] >= 400
        ) {

            echo "Jira Error:\n";

            print_r(
                $response['body']
            );

            return null;
        }

        return
            $response['body']['key']
            ?? null;
    }

    public function assignIssue(
        $issueKey,
        $accountId
    ) {

        return $this->request(
            'PUT',
            "/rest/api/3/issue/{$issueKey}/assignee",
            [
                'accountId' => $accountId
            ]
        );
    }

    public function getTransitions(
        $issueKey
    ) {

        $response =
            $this->request(
                'GET',
                "/rest/api/3/issue/{$issueKey}/transitions"
            );

        if (
            !isset(
                $response['body']['transitions']
            )
        ) {

            echo
            "Could not fetch transitions\n";

            print_r(
                $response['body']
            );

            return [];
        }

        return
            $response['body']['transitions'];
    }

    public function transitionIssue(
        $issueKey,
        $transitionId
    ) {

        return $this->request(
            'POST',
            "/rest/api/3/issue/{$issueKey}/transitions",
            [
                'transition' => [
                    'id' => $transitionId
                ]
            ]
        );
    }

    public function updateEstimate(
        $issueKey,
        $time
    ) {

        return $this->request(
            'PUT',
            "/rest/api/3/issue/{$issueKey}",
            [
                'fields' => [
                    'timetracking' => [
                        'originalEstimate' => $time
                    ]
                ]
            ]
        );
    }

    public function logWork(
        $issueKey,
        $time,
        $workDescription,
        $startDate = null
    ) {

        $payload = [
            'timeSpent' => $time
        ];

        if (trim($workDescription) !== '') {
            $adf = $this->htmlToAdf($workDescription);
            if ($adf) {
                $payload['comment'] = $adf;
            }
        }

        if (!empty($startDate)) {
            $started = DateTime::createFromFormat('Y-m-d', $startDate);
            if ($started) {
                $now = new DateTime();
                $started->setTime(
                    (int) $now->format('H'),
                    (int) $now->format('i'),
                    (int) $now->format('s')
                );
                $payload['started'] = $started->format('Y-m-d\TH:i:s.vO');
            }
        }

        return $this->request(
            'POST',
            "/rest/api/3/issue/{$issueKey}/worklog",
            $payload
        );
    }
}

$jira =
    new JiraLogger();

$titles =
    $_POST['title'];

$taskCategories =
    $_POST['task_category'];

$times =
    $_POST['time'];

$projects =
    $_POST['project_key'];

$startDates =
    $_POST['start_date'];

$dueDates =
    $_POST['due_date'];

$taskSizes =
    $_POST['task_size'];

$workDescriptions =
    $_POST['work_description'] ?? [];

foreach (
    $titles as $index => $title
) {

    echo
    "====================================\n";

    echo
    "Processing Task: {$title}\n";

    $issueKey =
        $jira->createIssue(
            $projects[$index],
            $title,
            $taskCategories[$index],
            $startDates[$index],
            $dueDates[$index],
            $taskSizes[$index]
        );

    if (!$issueKey) {

        echo
        "Issue creation failed\n";

        continue;
    }

    echo
    "Created: {$issueKey}\n";

    $jira->assignIssue(
        $issueKey,
        $jira->accountId
    );

    echo
    "Assigned To Wasik Bin Mashuk\n";

    $transitions =
        $jira->getTransitions(
            $issueKey
        );

    $inProgressId = null;
    $doneId = null;

    foreach (
        $transitions
        as $transition
    ) {

        $name =
            strtolower(
                $transition['name']
            );

        if (
            str_contains(
                $name,
                'progress'
            )
            ||
            str_contains(
                $name,
                'development'
            )
            ||
            str_contains(
                $name,
                'start'
            )
            ||
            str_contains(
                $name,
                'active'
            )
        ) {

            $inProgressId =
                $transition['id'];
        }

        if (
            $name === 'done'
            ||
            str_contains($name, 'done')
        ) {

            $doneId =
                $transition['id'];
        }
    }

    if ($inProgressId) {

        $jira->transitionIssue(
            $issueKey,
            $inProgressId
        );

        echo
        "Moved To In Progress\n";
    }

    $jira->updateEstimate(
        $issueKey,
        $times[$index]
    );

    echo
    "Estimate Updated\n";

    $workDescription =
        $workDescriptions[$index] ?? '';

    $jira->logWork(
        $issueKey,
        $times[$index],
        $workDescription,
        $startDates[$index]
    );

    echo
    "Work Logged\n";

    if ($doneId) {

        $jira->transitionIssue(
            $issueKey,
            $doneId
        );

        echo
        "Moved To Done\n";
    }

    echo
    "Completed Successfully\n\n";
}
