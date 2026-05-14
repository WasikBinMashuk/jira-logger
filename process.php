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
        $envFile = __DIR__ . '/.env';
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
        $time
    ) {

        return $this->request(
            'POST',
            "/rest/api/3/issue/{$issueKey}/worklog",
            [
                'timeSpent' => $time
            ]
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

    $jira->logWork(
        $issueKey,
        $times[$index]
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
