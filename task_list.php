<?php
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
}

$projectsFile = __DIR__ . '/data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projectsData = json_decode(file_get_contents($projectsFile), true);
    if (is_array($projectsData)) {
        $projects = $projectsData;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List – Jira Bulk Logger</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <style>

        :root {
            --bg-1: #f3f7fb;
            --bg-2: #e7eef8;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --accent: #0ea5e9;
            --accent-2: #14b8a6;
            --ring: rgba(14, 165, 233, 0.22);
            --shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
        }

        body {
            background:
                radial-gradient(1200px circle at 10% -10%, #dbeafe 0%, transparent 55%),
                radial-gradient(1000px circle at 110% 10%, #ccfbf1 0%, transparent 55%),
                linear-gradient(180deg, var(--bg-1), var(--bg-2));
            color: var(--ink);
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .main-card {
            max-width: 1400px;
            margin: 40px auto;
            border: 1px solid #e6ebf2;
            border-radius: 22px;
            box-shadow: var(--shadow);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            padding: 26px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-shadow: 0 6px 24px rgba(15, 23, 42, 0.2);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-body {
            padding: 40px 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 11px 16px;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem var(--ring);
            background: #ffffff;
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
            border: none;
            box-shadow: 0 12px 24px rgba(14, 165, 233, 0.28);
        }

        .btn-primary:hover {
            box-shadow: 0 16px 32px rgba(14, 165, 233, 0.35);
            transform: translateY(-1px);
        }

        .btn-light {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #0f172a;
        }

        .btn-light:hover {
            background: #eef2f7;
            color: #0f172a;
        }

        .btn-dark {
            background: #0f172a;
            border: 1px solid #0f172a;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.25);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            min-height: calc(1.5em + 22px + 2px);
            padding: 11px 42px 11px 16px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            background-color: #f8fafc;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: 1.5;
            color: #212529;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 12px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem var(--ring);
            background: #ffffff;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 12px;
            box-shadow: none;
            background: #f8fafc;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem var(--ring);
            outline: none;
            background: #ffffff;
        }

        .filter-panel {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e5eaf1;
            border-radius: 18px;
            padding: 26px;
            margin-bottom: 30px;
        }

        .results-panel {
            border: 1px solid #e5eaf1;
            border-radius: 18px;
            overflow: hidden;
        }

        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        .results-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .results-count {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 600;
        }

        .task-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
        }

        .task-table thead th {
            background: #f1f5f9;
            color: var(--muted);
            font-weight: 700;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .task-table tbody td {
            padding: 14px 18px;
            font-size: 0.92rem;
            border-bottom: 0;
            vertical-align: middle;
        }

        .task-table tbody tr {
            position: relative;
        }

        .task-table tbody tr::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .task-table tbody tr:last-child::after {
            display: none;
        }

        .task-table tbody tr:hover td {
            background: #f8fafc;
        }

        .issue-key {
            font-weight: 700;
            color: var(--accent);
            text-decoration: none;
            font-size: 0.88rem;
        }

        .issue-key:hover {
            color: var(--accent-2);
            text-decoration: underline;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .status-done {
            background: #d1fae5;
            color: #065f46;
        }

        .status-progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-todo {
            background: #f1f5f9;
            color: #475569;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.4;
            display: block;
        }

        .projects-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .projects-table tbody td {
            border-bottom: 0;
        }

        .projects-table tbody tr {
            position: relative;
        }

        .projects-table tbody tr::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .projects-table tbody tr:last-child::after {
            display: none;
        }

        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
            border: none;
        }

        .btn-danger:hover {
            background: #fca5a5;
            color: #991b1b;
        }

        .btn-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.18);
        }

        .btn-success:hover {
            background: #d1fae5;
            color: #065f46;
            transform: translateY(-1px);
        }

    </style>

</head>
<body>

<div class="container">

    <div class="card main-card">

        <div class="card-header">
            <div class="header-title">
                <i class="bi bi-list-task"></i> Task List
            </div>
            <div>
                <button type="button" class="btn btn-dark btn-sm fw-bold me-2" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="bi bi-gear-fill"></i>
                </button>
                <button type="button" class="btn btn-light btn-sm fw-bold me-2" data-bs-toggle="modal" data-bs-target="#projectsModal">
                    <i class="bi bi-folder2-open"></i> Projects
                </button>
                <a href="index.php" class="btn btn-light btn-sm fw-bold me-2">
                    <i class="bi bi-clock-history"></i> Daily Logger
                </a>
                <a href="future.php" class="btn btn-light btn-sm fw-bold">
                    <i class="bi bi-calendar-plus"></i> Future Tasks Planner
                </a>
            </div>
        </div>

        <div class="card-body">

            <!-- Filter Panel -->
            <div class="filter-panel">
                <form id="filterForm">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">Project</label>
                            <select class="form-select" name="project" id="projectFilter">
                                <option value="">All Projects</option>
                                <?php foreach ($projects as $project) { ?>
                                    <option value="<?php echo htmlspecialchars($project['key']); ?>">
                                        <?php echo htmlspecialchars($project['title']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Start Date From</label>
                            <input
                                type="text"
                                class="form-control date-picker"
                                name="start_date_from"
                                placeholder="YYYY-MM-DD"
                                autocomplete="off"
                                onkeydown="return false;"
                                onpaste="return false;"
                                style="cursor: pointer; caret-color: transparent;"
                            >
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Start Date To</label>
                            <input
                                type="text"
                                class="form-control date-picker"
                                name="start_date_to"
                                placeholder="YYYY-MM-DD"
                                autocomplete="off"
                                onkeydown="return false;"
                                onpaste="return false;"
                                style="cursor: pointer; caret-color: transparent;"
                            >
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Due Date From</label>
                            <input
                                type="text"
                                class="form-control date-picker"
                                name="due_date_from"
                                placeholder="YYYY-MM-DD"
                                autocomplete="off"
                                onkeydown="return false;"
                                onpaste="return false;"
                                style="cursor: pointer; caret-color: transparent;"
                            >
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Due Date To</label>
                            <input
                                type="text"
                                class="form-control date-picker"
                                name="due_date_to"
                                placeholder="YYYY-MM-DD"
                                autocomplete="off"
                                onkeydown="return false;"
                                onpaste="return false;"
                                style="cursor: pointer; caret-color: transparent;"
                            >
                        </div>

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100" id="searchBtn">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Results Panel -->
            <div class="results-panel">
                <div class="results-header">
                    <span class="results-title">
                        <i class="bi bi-table"></i> Results
                    </span>
                    <span class="results-count" id="resultsCount"></span>
                </div>

                <div class="table-responsive" id="resultsContainer">
                    <div class="empty-state">
                        <i class="bi bi-search"></i>
                        <div>Use the filters above and click Search to load your tasks.</div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingsModalLabel"><i class="bi bi-gear-fill"></i> Jira Credentials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="settingsForm">
                    <div class="mb-3">
                        <label class="form-label">Base URL</label>
                        <input type="url" class="form-control" name="jira_base_url" value="<?php echo htmlspecialchars($env['JIRA_BASE_URL'] ?? ''); ?>" required placeholder="https://your-domain.atlassian.net">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="jira_email" value="<?php echo htmlspecialchars($env['JIRA_EMAIL'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="text" class="form-control" name="jira_api_token" value="<?php echo htmlspecialchars($env['JIRA_API_TOKEN'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account ID</label>
                        <input type="text" class="form-control" name="jira_account_id" value="<?php echo htmlspecialchars($env['JIRA_ACCOUNT_ID'] ?? ''); ?>" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="saveSettingsBtn">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Projects Modal -->
<div class="modal fade" id="projectsModal" tabindex="-1" aria-labelledby="projectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectsModalLabel"><i class="bi bi-folder2-open"></i> Manage Projects</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Add New Project</label>
                    <form id="addProjectForm">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="projectKeyInput" placeholder="KEY" maxlength="10" required>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="projectTitleInput" placeholder="Project Title" required>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="button" class="btn btn-primary" id="addProjectBtn">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle projects-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Key</th>
                                <th>Title</th>
                                <th style="width: 25%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="projectsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>

if (window.jQuery && $.fn.select2) {
    $('#projectFilter').select2({
        width: '100%'
    });
}

flatpickr(".date-picker", {
    dateFormat: "Y-m-d",
    allowInput: true,
    onReady(_, __, fp) { fp.input.setAttribute("autocomplete", "off"); }
});

// ── Filter / Search ──────────────────────────────────────────────────────────

const filterForm = document.getElementById('filterForm');

async function fetchTasks() {
    if (!filterForm) {
        return;
    }

    const data = new FormData(filterForm);
    const startFrom = data.get('start_date_from');
    const startTo   = data.get('start_date_to');
    const dueFrom   = data.get('due_date_from');
    const dueTo     = data.get('due_date_to');

    if ((startFrom && !startTo) || (!startFrom && startTo)) {
        showAlert('Both Start Date From and To are required.', 'warning');
        return;
    }
    if ((dueFrom && !dueTo) || (!dueFrom && dueTo)) {
        showAlert('Both Due Date From and To are required.', 'warning');
        return;
    }
    if (!startFrom && !startTo && !dueFrom && !dueTo) {
        showAlert('Please set at least a Start Date or Due Date range to search.', 'warning');
        return;
    }

    const btn = document.getElementById('searchBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    }

    try {
        const response = await fetch('api/fetch_tasks.php', { method: 'POST', body: data });
        const result = await response.json();

        if (!result.success) {
            showAlert(result.message || 'Failed to fetch tasks.', 'error');
            return;
        }

        renderResults(result.issues, result.total, result.total_spent_secs || 0);

    } catch (err) {
        showAlert('Request failed: ' + err.message, 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search"></i>';
        }
    }
}

if (filterForm) {
    filterForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        await fetchTasks();
    });
}

function formatSeconds(secs) {
    if (!secs) return '0h';
    const h = Math.floor(secs / 3600);
    const m = Math.floor((secs % 3600) / 60);
    return h > 0 && m > 0 ? h + 'h ' + m + 'm' : h > 0 ? h + 'h' : m + 'm';
}

function renderResults(issues, total, totalSpentSecs) {
    const container   = document.getElementById('resultsContainer');
    const countEl     = document.getElementById('resultsCount');
    const baseUrl     = '<?php echo htmlspecialchars(rtrim($env['JIRA_BASE_URL'] ?? '', '/')); ?>';

    const count = issues.length;
    const taskLabel = count + ' task' + (count !== 1 ? 's' : '') + ' found' + (total > count ? ' (' + total + ' total)' : '');
    const loggedLabel = 'Total Logged: <strong>' + formatSeconds(totalSpentSecs) + '</strong>';
    countEl.innerHTML = taskLabel + ' &nbsp;|&nbsp; ' + loggedLabel;

    if (!issues.length) {
        container.innerHTML = '<div class="empty-state"><i class="bi bi-inbox"></i><div>No tasks found for the selected filters.</div></div>';
        return;
    }

    const rows = issues.map(issue => {
        const keyLink = baseUrl
            ? `<a class="issue-key" href="${escapeHtml(baseUrl)}/browse/${escapeHtml(issue.key)}" target="_blank">${escapeHtml(issue.key)}</a>`
            : `<span class="issue-key">${escapeHtml(issue.key)}</span>`;

        const statusClass = getStatusClass(issue.status);

        return `<tr data-issue-key="${escapeHtml(issue.key)}">
            <td>${keyLink}</td>
            <td>${escapeHtml(issue.summary)}</td>
            <td>${escapeHtml(issue.project)}</td>
            <td>${escapeHtml(issue.start_date)}</td>
            <td>${escapeHtml(issue.due_date)}</td>
            <td><span class="status-badge ${statusClass}">${escapeHtml(issue.status)}</span></td>
            <td>${escapeHtml(issue.original_estimate)}</td>
            <td>${escapeHtml(issue.time_spent)}</td>
            <td>${escapeHtml(issue.created)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger delete-task" data-issue-key="${escapeHtml(issue.key)}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');

    container.innerHTML = `
        <table class="task-table">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Summary</th>
                    <th>Project</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Estimate</th>
                    <th>Logged</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
}

function getStatusClass(status) {
    const s = (status || '').toLowerCase();
    if (s.includes('done') || s.includes('closed') || s.includes('resolved')) return 'status-done';
    if (s.includes('progress') || s.includes('review') || s.includes('active')) return 'status-progress';
    return 'status-todo';
}

const resultsContainer = document.getElementById('resultsContainer');
if (resultsContainer) {
    resultsContainer.addEventListener('click', async function (event) {
        const deleteBtn = event.target.closest('.delete-task');
        if (!deleteBtn) {
            return;
        }

        const issueKey = deleteBtn.getAttribute('data-issue-key');
        if (!issueKey) {
            return;
        }

        const confirmation = await Swal.fire({
            title: 'Delete task?',
            text: `This will delete ${issueKey} from Jira.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444'
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        deleteBtn.disabled = true;

        try {
            const response = await fetch('api/delete_task.php', {
                method: 'POST',
                body: new URLSearchParams({ key: issueKey })
            });
            const result = await response.json();

            if (!result.success) {
                showAlert(result.message || 'Failed to delete task.', 'error');
                deleteBtn.disabled = false;
                return;
            }

            showAlert(`${issueKey} deleted.`, 'success');
            await fetchTasks();
        } catch (err) {
            showAlert('Delete failed: ' + err.message, 'error');
            deleteBtn.disabled = false;
        }
    });
}

// ── Settings ─────────────────────────────────────────────────────────────────

document.getElementById('settingsForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn = document.getElementById('saveSettingsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

    try {
        const response = await fetch('api/save_settings.php', { method: 'POST', body: new FormData(this) });
        const result = await response.text();

        showAlert(result || 'Settings saved successfully.', 'success');

        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
            if (modal) modal.hide();
        }, 600);

    } catch (err) {
        showAlert('Error saving settings: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Save Settings';
    }
});

// ── Projects Modal ────────────────────────────────────────────────────────────

const projectsModal     = document.getElementById('projectsModal');
const projectsTableBody = document.getElementById('projectsTableBody');
const addProjectForm    = document.getElementById('addProjectForm');
const projectKeyInput   = document.getElementById('projectKeyInput');
const projectTitleInput = document.getElementById('projectTitleInput');
const addProjectBtn     = document.getElementById('addProjectBtn');

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showAlert(message, type) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        timer: 1800,
        showConfirmButton: false,
        timerProgressBar: true
    });
}

function renderProjects(projects) {
    projectsTableBody.innerHTML = projects.map(project => `
        <tr data-old-key="${escapeHtml(project.key)}">
            <td><input type="text" class="form-control form-control-sm project-key" value="${escapeHtml(project.key)}" maxlength="10"></td>
            <td><input type="text" class="form-control form-control-sm project-title" value="${escapeHtml(project.title)}"></td>
            <td class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary save-project">Save</button>
                <button type="button" class="btn btn-sm btn-danger delete-project">Delete</button>
            </td>
        </tr>
    `).join('');
}

function refreshProjectFilter(projects) {
    const select = document.getElementById('projectFilter');
    const current = select.value;
    select.innerHTML = '<option value="">All Projects</option>';
    projects.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.key;
        opt.textContent = p.title;
        select.appendChild(opt);
    });
    if (projects.some(p => p.key === current)) select.value = current;

    if (window.jQuery && $.fn.select2) {
        $(select).trigger('change.select2');
    }
}

async function loadProjects() {
    const response = await fetch('api/manage_projects.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'list' })
    });
    const result = await response.json();
    if (!result.success) { showAlert(result.message || 'Failed to load projects.', 'error'); return; }
    renderProjects(result.projects || []);
    refreshProjectFilter(result.projects || []);
}

if (projectsModal) {
    projectsModal.addEventListener('show.bs.modal', loadProjects);
}

if (addProjectBtn) {
    addProjectBtn.addEventListener('click', async function () {
        if (addProjectForm && !addProjectForm.checkValidity()) { addProjectForm.reportValidity(); return; }
        const key = projectKeyInput.value.trim();
        const title = projectTitleInput.value.trim();
        const response = await fetch('api/manage_projects.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'create', key, title })
        });
        const result = await response.json();
        if (!result.success) { showAlert(result.message || 'Failed to add project.', 'error'); return; }
        projectKeyInput.value = '';
        projectTitleInput.value = '';
        if (addProjectForm) addProjectForm.reset();
        renderProjects(result.projects || []);
        refreshProjectFilter(result.projects || []);
        showAlert('Project added.', 'success');
    });
}

projectsTableBody.addEventListener('click', async function (event) {
    const row = event.target.closest('tr');
    if (!row) return;

    if (event.target.classList.contains('save-project')) {
        const oldKey = row.getAttribute('data-old-key') || '';
        const key    = row.querySelector('.project-key').value.trim();
        const title  = row.querySelector('.project-title').value.trim();
        const response = await fetch('api/manage_projects.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'update', old_key: oldKey, key, title })
        });
        const result = await response.json();
        if (!result.success) { showAlert(result.message || 'Failed to update project.', 'error'); return; }
        renderProjects(result.projects || []);
        refreshProjectFilter(result.projects || []);
        showAlert('Project updated successfully.', 'success');
        setTimeout(() => { const m = bootstrap.Modal.getInstance(projectsModal); if (m) m.hide(); }, 600);
        return;
    }

    if (event.target.classList.contains('delete-project')) {
        const key = row.getAttribute('data-old-key') || '';
        const confirmation = await Swal.fire({
            title: 'Delete project?',
            text: `Project ${key} will be removed from the list.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444'
        });
        if (!confirmation.isConfirmed) return;
        const response = await fetch('api/manage_projects.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'delete', key })
        });
        const result = await response.json();
        if (!result.success) { showAlert(result.message || 'Failed to delete project.', 'error'); return; }
        renderProjects(result.projects || []);
        refreshProjectFilter(result.projects || []);
        showAlert('Project removed successfully.', 'success');
        setTimeout(() => { const m = bootstrap.Modal.getInstance(projectsModal); if (m) m.hide(); }, 600);
    }
});

</script>

</body>
</html>
