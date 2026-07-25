<?php

header('Content-Type: application/json');

$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
}

$baseUrl = rtrim($env['JIRA_BASE_URL'] ?? '', '/');
$email   = $env['JIRA_EMAIL'] ?? '';
$token   = $env['JIRA_API_TOKEN'] ?? '';

if (!$baseUrl || !$email || !$token) {
    echo json_encode(['success' => false, 'message' => 'Jira credentials not configured. Please update settings.']);
    exit;
}

$projectsFile = __DIR__ . '/../data/projects.json';
$projectKeys  = [];
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true);
    if (is_array($projects)) {
        foreach ($projects as $project) {
            if (!empty($project['key'])) {
                $projectKeys[] = $project['key'];
            }
        }
    }
}

if (!$projectKeys) {
    echo json_encode(['success' => true, 'teams' => []]);
    exit;
}

$quotedKeys = array_map(fn($key) => '"' . $key . '"', $projectKeys);
$jql        = 'project in (' . implode(',', $quotedKeys) . ') AND cf[10500] is not EMPTY ORDER BY updated DESC';

$url     = $baseUrl . '/rest/api/3/search/jql';
$payload = json_encode([
    'jql'        => $jql,
    'maxResults' => 100,
    'fields'     => ['customfield_10500'],
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

$teams = [];
foreach ($data['issues'] ?? [] as $issue) {
    $team = $issue['fields']['customfield_10500'] ?? null;
    if ($team && !empty($team['id']) && !isset($teams[$team['id']])) {
        $teams[$team['id']] = [
            'id'   => $team['id'],
            'name' => $team['name'] ?? $team['title'] ?? $team['id'],
        ];
    }
}

echo json_encode([
    'success' => true,
    'teams'   => array_values($teams),
]);
