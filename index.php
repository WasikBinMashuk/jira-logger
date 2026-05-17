<?php
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
}

$projectsFile = __DIR__ . '/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projectsData = json_decode(file_get_contents($projectsFile), true);
    if (is_array($projectsData)) {
        $projects = $projectsData;
    }
}

// If projects.json is missing or empty, show only the placeholder.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jira Bulk Logger</title>

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

        .task-row {
            border: 1px solid #e5eaf1;
            padding: 26px;
            border-radius: 18px;
            margin-bottom: 25px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            position: relative;
        }

        .task-row:hover {
            border-color: #d6dde8;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            background: #ffffff;
            transform: translateY(-2px);
        }

        .task-number {
            font-size: 0.85rem;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(20, 184, 166, 0.2));
            color: #0f172a;
            border: 1px solid rgba(14, 165, 233, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
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

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
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

        .remove-btn {
            position: absolute;
            top: -12px;
            right: -12px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: #ef4444;
            color: white;
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
            border: 2px solid white;
            z-index: 10;
        }

        .remove-btn:hover {
            background: #dc2626;
            color: white;
            transform: scale(1.1);
        }

        .response-box {
            background: #0f172a;
            color: #38bdf8;
            min-height: 250px;
            border-radius: 16px;
            padding: 25px;
            font-family: 'Fira Code', monospace;
            font-size: 0.95rem;
            white-space: pre-wrap;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
            border: 1px solid #1e293b;
        }

        .response-box-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #1e293b;
            font-weight: 700;
        }

    </style>

</head>
<body>

<div class="container">

    <div class="card main-card">

        <div class="card-header">
            <div class="header-title">
                <i class="bi bi-jira"></i> Daily Jira Work Logger
            </div>
            <div>
                <button type="button" class="btn btn-dark btn-sm fw-bold me-2" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="bi bi-gear-fill"></i>
                </button>
                <button type="button" class="btn btn-light btn-sm fw-bold me-2" data-bs-toggle="modal" data-bs-target="#projectsModal">
                    <i class="bi bi-folder2-open"></i> Projects
                </button>
                <a href="task_list.php" class="btn btn-light btn-sm fw-bold me-2">
                    <i class="bi bi-list-task"></i> Task List
                </a>
                <a href="future.php" class="btn btn-light btn-sm fw-bold">
                    <i class="bi bi-calendar-plus"></i> Future Tasks Planner
                </a>
            </div>
        </div>

        <div class="card-body">

            <form id="jiraForm">

                <div id="taskContainer">

                    <div class="task-row">
                        <h6 class="task-number badge bg-secondary mb-3">Task #1</h6>

                        <div class="row">

                            <!-- FIRST ROW (12 Cols) -->
                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Project
                                </label>

                                <select
                                    class="form-select"
                                    name="project_key[]"
                                    required
                                >

                                    <option value="">
                                        Select
                                    </option>

                                    <?php foreach ($projects as $project) { ?>
                                        <option value="<?php echo htmlspecialchars($project['key']); ?>">
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </option>
                                    <?php } ?>

                                </select>

                            </div>

                            <div class="col-md-7 mb-3">

                                <label class="form-label">
                                    Task Title
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="title[]"
                                    placeholder="Enter task title"
                                    required
                                >

                            </div>

                            <div class="col-md-2 mb-3">

                                <label class="form-label">
                                    Time
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="time[]"
                                    placeholder="2h"
                                    required
                                >

                            </div>

                            <!-- SECOND ROW (12 Cols) -->
                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Start Date
                                </label>

                                <input
                                    type="text"
                                    class="form-control date-picker"
                                    name="start_date[]"
                                    placeholder="YYYY-MM-DD"
                                    required
                                    onkeydown="return false;"
                                    onpaste="return false;"
                                    style="cursor: pointer; background-color: #fff; caret-color: transparent;"
                                >

                            </div>

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Due Date
                                </label>

                                <input
                                    type="text"
                                    class="form-control date-picker"
                                    name="due_date[]"
                                    placeholder="YYYY-MM-DD"
                                    required
                                    onkeydown="return false;"
                                    onpaste="return false;"
                                    style="cursor: pointer; background-color: #fff; caret-color: transparent;"
                                >

                            </div>

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Task Size
                                </label>

                                <select
                                    class="form-select"
                                    name="task_size[]"
                                    required
                                >

                                    <option value="">
                                        Select
                                    </option>

                                    <option value="10664">
                                        Extra Small (XS)
                                    </option>

                                    <option value="10665" selected>
                                        Small (S)
                                    </option>

                                    <option value="10666">
                                        Medium (M)
                                    </option>

                                    <option value="10667">
                                        Large (L)
                                    </option>

                                    <option value="10668">
                                        Extra Large (XL)
                                    </option>

                                </select>

                            </div>

                            <!-- THIRD ROW (12 Cols) -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Task Category
                                </label>

                                <select
                                    class="form-select"
                                    name="task_category[]"
                                    required
                                >
                                    <option value="">Select</option>
                                    <option value="10565">Business Requirement Analysis</option>
                                    <option value="10566" selected>Development & Unit Testing</option>
                                    <option value="10567">Technical Analysis</option>
                                    <option value="10568">UX Design</option>
                                    <option value="10569">Technical Design</option>
                                    <option value="10570">Bug fix</option>
                                    <option value="10571">Deployment</option>
                                    <option value="10572">Code Review</option>
                                    <option value="10573">Testing and UAT</option>
                                    <option value="10574">Support & Monitoring</option>
                                    <option value="10575">Team Management</option>
                                    <option value="10576">Meeting</option>
                                    <option value="10653">RnD</option>
                                    <option value="10656">Product Management</option>
                                    <option value="10669">PMO - Follow Up</option>
                                    <option value="10671">PMO - Scrum Meeting</option>
                                    <option value="10672">PMO - BRD/SRS/Project Schedule</option>
                                </select>

                            </div>

                        </div>

                        <button
                            type="button"
                            class="btn remove-btn"
                            title="Remove Task"
                        >
                            <i class="bi bi-x"></i>
                        </button>

                    </div>

                </div>

                <div class="d-flex gap-3 mt-4">

                    <button
                        type="button"
                        id="addMore"
                        class="btn btn-success"
                    >
                        <i class="bi bi-plus-circle me-1"></i> Add Another Task
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="submitJiraFormBtn"
                    >
                        <i class="bi bi-cloud-arrow-up me-1"></i> Submit All Tasks
                    </button>

                </div>

            </form>

            <hr class="my-5">

            <h5 class="response-box-title">
                <i class="bi bi-terminal"></i> Response Log
            </h5>

            <div
                class="response-box"
                id="responseBox"
            >System ready. Waiting for task submission...</div>

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
                            <button type="button" class="btn btn-primary" id="addProjectBtn">
                                Add
                            </button>
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

// Initialize flatpickr on page load
flatpickr(".date-picker", {
    dateFormat: "Y-m-d",
    allowInput: true
});

if (window.jQuery && $.fn.select2) {
    $('.form-select').select2({
        width: '100%'
    });
}

const container =
    document.getElementById('taskContainer');

document
    .getElementById('addMore')
    .addEventListener('click', function() {

        const firstRow =
            document.querySelector('.task-row');

        const clone =
            firstRow.cloneNode(true);

        clone.querySelectorAll('input').forEach(input => {
            input.value = '';
            // Reset flatpickr specifics if cloned
            if (input.classList.contains('flatpickr-input')) {
                input.classList.remove('flatpickr-input', 'active');
                input.removeAttribute('readonly');
            }
        });

        clone.querySelectorAll('select').forEach(select => {
            const defaultOption = Array.from(select.options).findIndex(opt => opt.hasAttribute('selected'));
            select.selectedIndex = defaultOption !== -1 ? defaultOption : 0;
        });

        if (window.jQuery && $.fn.select2) {
            clone.querySelectorAll('.select2-container').forEach(el => el.remove());
            clone.querySelectorAll('select').forEach(select => {
                select.classList.remove('select2-hidden-accessible');
                select.removeAttribute('data-select2-id');
                select.removeAttribute('tabindex');
                select.removeAttribute('aria-hidden');
                select.querySelectorAll('option').forEach(opt => opt.removeAttribute('data-select2-id'));
                $(select).select2({
                    width: '100%'
                });
            });
        }

        container.appendChild(clone);
        updateTaskNumbers();
        
        // Re-initialize flatpickr on the new row only
        flatpickr(clone.querySelectorAll(".date-picker"), {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    });

function updateTaskNumbers() {
    const rows = document.querySelectorAll('.task-row');
    rows.forEach((row, index) => {
        const numberElement = row.querySelector('.task-number');
        if (numberElement) {
            numberElement.textContent = `Task #${index + 1}`;
        }
    });
}

document.addEventListener('click', function(e) {

    const removeBtn = e.target.closest('.remove-btn');

    if (removeBtn) {

        const rows =
            document.querySelectorAll('.task-row');

        if (rows.length > 1) {
            removeBtn.closest('.task-row').remove();
            updateTaskNumbers();
        }
    }
});

document.addEventListener('change', function(e) {
    if (e.target.name === 'start_date[]') {
        const row = e.target.closest('.task-row');
        if (row) {
            const dueDate = row.querySelector('input[name="due_date[]"]');
            if (dueDate) {
                if (dueDate._flatpickr) {
                    dueDate._flatpickr.setDate(e.target.value);
                } else {
                    dueDate.value = e.target.value;
                }
            }
        }
    }
});

document
    .getElementById('jiraForm')
    .addEventListener('submit', async function(e) {

        e.preventDefault();

        const responseBox =
            document.getElementById('responseBox');
            
        const submitBtn =
            document.getElementById('submitJiraFormBtn');
            
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';

        responseBox.innerHTML =
            'Processing...\n';

        const formData =
            new FormData(this);

        try {

            const response = await fetch(
                'process.php',
                {
                    method: 'POST',
                    body: formData
                }
            );

            const result =
                await response.text();

            responseBox.innerHTML = result;

            // Clear the form if the request was successful
            this.reset();

            if (window.jQuery && $.fn.select2) {
                $('.form-select').each(function() {
                    const defaultOption = Array.from(this.options).findIndex(opt => opt.hasAttribute('selected'));
                    this.selectedIndex = defaultOption !== -1 ? defaultOption : 0;
                }).trigger('change.select2');
            }
            
            // Remove all dynamically added task rows except the first one
            const rows = document.querySelectorAll('.task-row');
            for (let i = 1; i < rows.length; i++) {
                rows[i].remove();
            }
            updateTaskNumbers();

        } catch (error) {

            responseBox.innerHTML =
                error.message;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('saveSettingsBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    try {
        const response = await fetch('save_settings.php', {
            method: 'POST',
            body: new FormData(this)
        });
        
        const result = await response.text();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: result || 'Settings saved successfully.',
            timer: 1800,
            showConfirmButton: false,
            timerProgressBar: true
        });

        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
            if (modal) {
                modal.hide();
            }
        }, 600);
        
    } catch (error) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Error saving settings: ' + error.message,
            timer: 2200,
            showConfirmButton: false,
            timerProgressBar: true
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Save Settings';
    }
});

const projectsModal = document.getElementById('projectsModal');
const projectsTableBody = document.getElementById('projectsTableBody');
const addProjectForm = document.getElementById('addProjectForm');
const projectKeyInput = document.getElementById('projectKeyInput');
const projectTitleInput = document.getElementById('projectTitleInput');
const addProjectBtn = document.getElementById('addProjectBtn');

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showProjectsAlert(message, type) {
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
            <td>
                <input type="text" class="form-control form-control-sm project-key" value="${escapeHtml(project.key)}" maxlength="10">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm project-title" value="${escapeHtml(project.title)}">
            </td>
            <td class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary save-project">Save</button>
                <button type="button" class="btn btn-sm btn-danger delete-project">Delete</button>
            </td>
        </tr>
    `).join('');
}

function refreshProjectSelects(projects) {
    const selects = document.querySelectorAll('select[name="project_key[]"]');
    const keys = projects.map(project => project.key);

    selects.forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select';
        select.appendChild(placeholder);

        projects.forEach(project => {
            const option = document.createElement('option');
            option.value = project.key;
            option.textContent = project.title;
            select.appendChild(option);
        });

        if (keys.includes(currentValue)) {
            select.value = currentValue;
        } else {
            select.value = '';
        }

        if (window.jQuery && $.fn.select2) {
            $(select).trigger('change.select2');
        }
    });
}

async function loadProjects() {
    const response = await fetch('manage_projects.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'list' })
    });

    const result = await response.json();

    if (!result.success) {
        showProjectsAlert(result.message || 'Failed to load projects.', 'error');
        return;
    }

    renderProjects(result.projects || []);
    refreshProjectSelects(result.projects || []);
}

if (projectsModal) {
    projectsModal.addEventListener('show.bs.modal', loadProjects);
}

if (addProjectBtn) {
    addProjectBtn.addEventListener('click', async function() {
        if (addProjectForm && !addProjectForm.checkValidity()) {
            addProjectForm.reportValidity();
            return;
        }

        const key = projectKeyInput.value.trim();
        const title = projectTitleInput.value.trim();

        const response = await fetch('manage_projects.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'create',
                key: key,
                title: title
            })
        });

        const result = await response.json();

        if (!result.success) {
            showProjectsAlert(result.message || 'Failed to add project.', 'error');
            return;
        }

        projectKeyInput.value = '';
        projectTitleInput.value = '';
        if (addProjectForm) {
            addProjectForm.reset();
        }
        renderProjects(result.projects || []);
        refreshProjectSelects(result.projects || []);
        showProjectsAlert('Project added.', 'success');
    });
}

projectsTableBody.addEventListener('click', async function(event) {
    const row = event.target.closest('tr');
    if (!row) {
        return;
    }

    if (event.target.classList.contains('save-project')) {
        const oldKey = row.getAttribute('data-old-key') || '';
        const key = row.querySelector('.project-key').value.trim();
        const title = row.querySelector('.project-title').value.trim();

        const response = await fetch('manage_projects.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'update',
                old_key: oldKey,
                key: key,
                title: title
            })
        });

        const result = await response.json();

        if (!result.success) {
            showProjectsAlert(result.message || 'Failed to update project.', 'error');
            return;
        }

        renderProjects(result.projects || []);
        refreshProjectSelects(result.projects || []);

        showProjectsAlert('Project updated successfully.', 'success');

        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(projectsModal);
            if (modal) {
                modal.hide();
            }
        }, 600);
        return;
    }

    if (event.target.classList.contains('delete-project')) {
        const key = row.getAttribute('data-old-key') || '';
        const confirmation = await Swal.fire({
            title: 'Delete project?'
            , text: `Project ${key} will be removed from the list.`
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Delete'
            , cancelButtonText: 'Cancel'
            , confirmButtonColor: '#ef4444'
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        const response = await fetch('manage_projects.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'delete',
                key: key
            })
        });

        const result = await response.json();

        if (!result.success) {
            showProjectsAlert(result.message || 'Failed to delete project.', 'error');
            return;
        }

        renderProjects(result.projects || []);
        refreshProjectSelects(result.projects || []);

        showProjectsAlert('Project removed successfully.', 'success');

        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(projectsModal);
            if (modal) {
                modal.hide();
            }
        }, 600);
    }
});

</script>

</body>
</html>