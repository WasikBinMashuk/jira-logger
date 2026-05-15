# Jira Logger

A lightweight Jira Cloud task logger with two modes:
- Daily Logger: create tasks, log work, and close
- Future Planner: create planned tasks without logging work

## Features
- Bulk task creation for Jira Cloud
- Two modes: Daily Logger and Future Planner
- Settings modal to save Jira credentials
- Project manager (add/edit/delete) stored in a local JSON file
- Date pickers and Select2 dropdowns

## Requirements
- PHP 7.4+ (with cURL enabled)
- Jira Cloud account and API token

## Setup
1. Copy the example env file and fill in values:
   - Windows (PowerShell):
     - Copy-Item .env.example .env
   - Bash:
     - cp .env.example .env

2. Update .env with your Jira details:
   - JIRA_BASE_URL (e.g., https://your-domain.atlassian.net)
   - JIRA_EMAIL
   - JIRA_API_TOKEN
   - JIRA_ACCOUNT_ID

3. Start a local PHP server:
   - php -S localhost:8000

4. Open the app:
   - Daily Logger: http://localhost:8000/index.php
   - Future Planner: http://localhost:8000/future.php

## Usage
- Open Settings and save your Jira credentials the first time.
- Daily Logger:
  - Fill in tasks, dates, size, and category
  - Submit to create issues, log time, and transition to Done
- Future Planner:
  - Set a single target date for all tasks
  - Submit to create issues only (no work log or status change)

## Project Management
- Projects are stored in projects.json and managed from the Daily Logger.
- If projects.json is missing, the Project dropdown shows only Select.
- Once you add projects, the dropdowns in both pages update.

## Files
- index.php: Daily Logger UI
- future.php: Future Planner UI
- process.php: Daily Logger backend
- process_future.php: Future Planner backend
- save_settings.php: Saves settings to .env
- manage_projects.php: CRUD for projects.json
- projects.json: Project list (local, ignored by git)

## Troubleshooting
- If requests fail, verify .env values and Jira permissions.
- Ensure cURL is enabled in PHP.
