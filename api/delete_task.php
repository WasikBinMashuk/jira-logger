<?php

header('Content-Type: application/json');

$envFile = __DIR__ . '/../.env';
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

$issueKey = trim($_POST['key'] ?? '');
if ($issueKey === '') {
    echo json_encode(['success' => false, 'message' => 'Issue key is required.']);
    exit;
}

$url = $baseUrl . '/rest/api/3/issue/' . rawurlencode($issueKey);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($email . ':' . $token),
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to Jira.']);
    exit;
}

$body = json_decode($response, true);

if ($httpCode !== 204) {
    $error = $body['errorMessages'][0] ?? $body['message'] ?? 'Failed to delete task.';
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

echo json_encode(['success' => true]);
