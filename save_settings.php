<?php

$envFile = __DIR__ . '/.env';

$baseUrl = $_POST['jira_base_url'] ?? '';
$email = $_POST['jira_email'] ?? '';
$apiToken = $_POST['jira_api_token'] ?? '';
$accountId = $_POST['jira_account_id'] ?? '';

// Build the env content
$envContent = "JIRA_BASE_URL=\"{$baseUrl}\"\n";
$envContent .= "JIRA_EMAIL=\"{$email}\"\n";
$envContent .= "JIRA_API_TOKEN=\"{$apiToken}\"\n";
$envContent .= "JIRA_ACCOUNT_ID=\"{$accountId}\"\n";

if (file_put_contents($envFile, $envContent) !== false) {
    echo "Settings saved successfully!";
} else {
    http_response_code(500);
    echo "Failed to save settings to .env file.";
}
