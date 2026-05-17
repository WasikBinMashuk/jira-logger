<?php

header('Content-Type: application/json');

$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
}

$baseUrl   = rtrim($env['JIRA_BASE_URL'] ?? '', '/');
$email     = $env['JIRA_EMAIL'] ?? '';
$token     = $env['JIRA_API_TOKEN'] ?? '';
$accountId = $env['JIRA_ACCOUNT_ID'] ?? '';

if (!$baseUrl || !$email || !$token || !$accountId) {
    echo json_encode(['success' => false, 'message' => 'Jira credentials not configured. Please update settings.']);
    exit;
}

$project      = trim($_POST['project'] ?? '');
$startFrom    = trim($_POST['start_date_from'] ?? '');
$startTo      = trim($_POST['start_date_to'] ?? '');
$dueFrom      = trim($_POST['due_date_from'] ?? '');
$dueTo        = trim($_POST['due_date_to'] ?? '');

$jql = 'reporter = "' . $accountId . '"';

if ($project !== '') {
    $jql .= ' AND project = "' . $project . '"';
}
if ($startFrom !== '') {
    $jql .= ' AND cf[10800] >= "' . $startFrom . '"';
}
if ($startTo !== '') {
    $jql .= ' AND cf[10800] <= "' . $startTo . '"';
}
if ($dueFrom !== '') {
    $jql .= ' AND duedate >= "' . $dueFrom . '"';
}
if ($dueTo !== '') {
    $jql .= ' AND duedate <= "' . $dueTo . '"';
}

$jql .= ' ORDER BY created DESC';

$url     = $baseUrl . '/rest/api/3/search/jql';
$payload = json_encode([
    'jql'        => $jql,
    'maxResults' => 50,
    'fields'     => ['summary', 'status', 'project', 'duedate', 'customfield_10800', 'created', 'timetracking'],
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($email . ':' . $token),
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to Jira.']);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200) {
    $error = $data['errorMessages'][0] ?? $data['message'] ?? 'Unknown error from Jira.';
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

$issues = [];
foreach ($data['issues'] ?? [] as $issue) {
    $fields   = $issue['fields'];
    $issues[] = [
        'key'               => $issue['key'],
        'summary'           => $fields['summary'] ?? '',
        'project'           => $fields['project']['name'] ?? '',
        'status'            => $fields['status']['name'] ?? '',
        'start_date'        => $fields['customfield_10800'] ?? '',
        'due_date'          => $fields['duedate'] ?? '',
        'created'           => substr($fields['created'] ?? '', 0, 10),
        'original_estimate' => $fields['timetracking']['originalEstimate'] ?? '',
        'time_spent'        => $fields['timetracking']['timeSpent'] ?? '',
    ];
}

echo json_encode([
    'success' => true,
    'issues'  => $issues,
    'total'   => $data['total'] ?? 0,
]);
