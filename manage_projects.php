<?php

header('Content-Type: application/json');

$projectsFile = __DIR__ . '/projects.json';

function loadProjects($projectsFile)
{
    if (!file_exists($projectsFile)) {
        return [];
    }

    $data = file_get_contents($projectsFile);
    $projects = json_decode($data, true);

    if (!is_array($projects)) {
        return [];
    }

    return $projects;
}

function saveProjects($projectsFile, $projects)
{
    $payload = json_encode(array_values($projects), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return file_put_contents($projectsFile, $payload) !== false;
}

$action = $_POST['action'] ?? '';
$projects = loadProjects($projectsFile);

if ($action === 'list') {
    echo json_encode(['success' => true, 'projects' => $projects]);
    exit;
}

$key = strtoupper(trim($_POST['key'] ?? ''));
$title = trim($_POST['title'] ?? '');

if ($action === 'create') {
    if ($key === '' || $title === '') {
        echo json_encode(['success' => false, 'message' => 'Key and title are required.']);
        exit;
    }

    foreach ($projects as $project) {
        if (strtoupper($project['key']) === $key) {
            echo json_encode(['success' => false, 'message' => 'Project key already exists.']);
            exit;
        }
    }

    $projects[] = ['key' => $key, 'title' => $title];

    if (!saveProjects($projectsFile, $projects)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save projects.']);
        exit;
    }

    echo json_encode(['success' => true, 'projects' => $projects]);
    exit;
}

if ($action === 'update') {
    $oldKey = strtoupper(trim($_POST['old_key'] ?? ''));

    if ($oldKey === '' || $key === '' || $title === '') {
        echo json_encode(['success' => false, 'message' => 'Key and title are required.']);
        exit;
    }

    foreach ($projects as $project) {
        if (strtoupper($project['key']) === $key && $key !== $oldKey) {
            echo json_encode(['success' => false, 'message' => 'Project key already exists.']);
            exit;
        }
    }

    $updated = false;
    foreach ($projects as $index => $project) {
        if (strtoupper($project['key']) === $oldKey) {
            $projects[$index] = ['key' => $key, 'title' => $title];
            $updated = true;
            break;
        }
    }

    if (!$updated) {
        echo json_encode(['success' => false, 'message' => 'Project not found.']);
        exit;
    }

    if (!saveProjects($projectsFile, $projects)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save projects.']);
        exit;
    }

    echo json_encode(['success' => true, 'projects' => $projects]);
    exit;
}

if ($action === 'delete') {
    if ($key === '') {
        echo json_encode(['success' => false, 'message' => 'Key is required.']);
        exit;
    }

    $projects = array_values(array_filter($projects, function ($project) use ($key) {
        return strtoupper($project['key']) !== $key;
    }));

    if (!saveProjects($projectsFile, $projects)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save projects.']);
        exit;
    }

    echo json_encode(['success' => true, 'projects' => $projects]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
