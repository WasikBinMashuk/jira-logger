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

// If projects.json is missing or empty, show only the placeholder.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jira Bulk Logger</title>

    <script src="theme.js"></script>

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
            --accent: #10b981;
            --accent-2: #0ea5e9;
            --ring: rgba(16, 185, 129, 0.22);
            --shadow: 0 28px 70px rgba(15, 23, 42, 0.12);
        }

        body {
            background:
                radial-gradient(1200px circle at 10% -10%, #dcfce7 0%, transparent 55%),
                radial-gradient(1000px circle at 110% 10%, #cffafe 0%, transparent 55%),
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

        .header-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            row-gap: 10px;
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 22px 20px;
            }

            .header-title {
                font-size: 19px;
            }

            .header-actions {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .header-title {
                font-size: 17px;
            }

            .header-actions .btn {
                font-size: 0.82rem;
                padding: 8px 12px;
            }
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
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.18), rgba(14, 165, 233, 0.2));
            color: #0f172a;
            border: 1px solid rgba(16, 185, 129, 0.25);
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
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.28);
        }

        .btn-primary:hover {
            box-shadow: 0 16px 32px rgba(16, 185, 129, 0.35);
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

        .target-date-box {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
        }

        .theme-toggle {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.26);
        }

        /* Dark mode */

        [data-theme="dark"] {
            --bg-1: #0b1220;
            --bg-2: #0a0f1a;
            --card: #111827;
            --ink: #e2e8f0;
            --muted: #94a3b8;
            --shadow: 0 28px 70px rgba(0, 0, 0, 0.55);
        }

        [data-theme="dark"] body {
            background:
                radial-gradient(1200px circle at 10% -10%, rgba(16, 185, 129, 0.12) 0%, transparent 55%),
                radial-gradient(1000px circle at 110% 10%, rgba(14, 165, 233, 0.12) 0%, transparent 55%),
                linear-gradient(180deg, var(--bg-1), var(--bg-2));
        }

        [data-theme="dark"] .main-card {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(17, 24, 39, 0.92);
            color: var(--ink);
        }

        [data-theme="dark"] .target-date-box {
            background: #16202f;
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .task-row {
            border-color: rgba(255, 255, 255, 0.08);
            background: linear-gradient(180deg, #16202f 0%, #131c29 100%);
        }

        [data-theme="dark"] .task-row:hover {
            border-color: rgba(255, 255, 255, 0.16);
            background: #16202f;
        }

        [data-theme="dark"] .task-number {
            color: #e2e8f0;
            border-color: rgba(16, 185, 129, 0.35);
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background: #1a2332;
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--ink);
        }

        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            background: #1e293b;
            color: var(--ink);
        }

        [data-theme="dark"] .form-control::placeholder {
            color: #64748b;
        }

        [data-theme="dark"] .form-control:-webkit-autofill,
        [data-theme="dark"] .form-control:-webkit-autofill:hover,
        [data-theme="dark"] .form-control:-webkit-autofill:focus,
        [data-theme="dark"] .form-control:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #1a2332 inset !important;
            box-shadow: 0 0 0 1000px #1a2332 inset !important;
            -webkit-text-fill-color: var(--ink) !important;
            caret-color: var(--ink);
            transition: background-color 5000s ease-in-out 0s;
        }

        [data-theme="dark"] .select2-container--default .select2-selection--single {
            background-color: #1a2332;
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--ink);
        }

        [data-theme="dark"] .select2-container--default.select2-container--focus .select2-selection--single,
        [data-theme="dark"] .select2-container--default.select2-container--open .select2-selection--single {
            background: #1e293b;
        }

        [data-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            background: #1a2332;
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--ink);
        }

        [data-theme="dark"] .select2-dropdown {
            background: #16202f;
            border-color: rgba(255, 255, 255, 0.12);
            color: var(--ink);
        }

        [data-theme="dark"] .select2-results__option {
            color: var(--ink);
        }

        [data-theme="dark"] .select2-results__option--selected {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--ink);
        }

        [data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--accent);
            color: #ffffff;
        }

        [data-theme="dark"] .select2-results__options {
            scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
            scrollbar-width: thin;
        }

        [data-theme="dark"] .select2-results__options::-webkit-scrollbar {
            width: 8px;
        }

        [data-theme="dark"] .select2-results__options::-webkit-scrollbar-track {
            background: transparent;
        }

        [data-theme="dark"] .select2-results__options::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.25);
            border-radius: 8px;
        }

        [data-theme="dark"] .select2-results__options::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }

        [data-theme="dark"] .btn-light {
            background: #1a2332;
            border-color: rgba(255, 255, 255, 0.12);
            color: var(--ink);
        }

        [data-theme="dark"] .btn-light:hover {
            background: #222e42;
            color: var(--ink);
        }

        [data-theme="dark"] .btn-dark {
            border-color: rgba(255, 255, 255, 0.12);
        }

        [data-theme="dark"] .btn-success {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.35);
        }

        [data-theme="dark"] .btn-success:hover {
            background: rgba(16, 185, 129, 0.26);
            color: #a7f3d0;
        }

        [data-theme="dark"] .remove-btn {
            border-color: var(--card);
        }

        [data-theme="dark"] .response-box-title {
            color: var(--ink);
        }

        [data-theme="dark"] .modal-content {
            background: var(--card);
            color: var(--ink);
        }

        [data-theme="dark"] .modal-header,
        [data-theme="dark"] .modal-footer {
            border-color: rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        [data-theme="dark"] .alert-info {
            background-color: rgba(14, 165, 233, 0.15);
            border-color: rgba(14, 165, 233, 0.35);
            color: #7dd3fc;
        }

        [data-theme="dark"] .alert-success {
            background-color: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }

        [data-theme="dark"] .alert-danger {
            background-color: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        [data-theme="dark"] .flatpickr-calendar {
            background: #16202f;
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.55);
        }

        [data-theme="dark"] .flatpickr-calendar.arrowTop:before {
            border-bottom-color: rgba(255, 255, 255, 0.12);
        }

        [data-theme="dark"] .flatpickr-calendar.arrowTop:after {
            border-bottom-color: #16202f;
        }

        [data-theme="dark"] .flatpickr-calendar.arrowBottom:before {
            border-top-color: rgba(255, 255, 255, 0.12);
        }

        [data-theme="dark"] .flatpickr-calendar.arrowBottom:after {
            border-top-color: #16202f;
        }

        [data-theme="dark"] .flatpickr-months .flatpickr-month,
        [data-theme="dark"] .flatpickr-weekdays,
        [data-theme="dark"] span.flatpickr-weekday,
        [data-theme="dark"] .flatpickr-days {
            background: #16202f;
            color: var(--ink);
            fill: var(--ink);
        }

        [data-theme="dark"] span.flatpickr-weekday {
            color: var(--muted);
        }

        [data-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months {
            background: #16202f;
            color: var(--ink);
        }

        [data-theme="dark"] .flatpickr-current-month input.cur-year {
            background: transparent;
            color: var(--ink);
        }

        [data-theme="dark"] .flatpickr-prev-month svg,
        [data-theme="dark"] .flatpickr-next-month svg {
            fill: var(--ink);
        }

        [data-theme="dark"] .flatpickr-prev-month:hover svg,
        [data-theme="dark"] .flatpickr-next-month:hover svg {
            fill: var(--accent);
        }

        [data-theme="dark"] .flatpickr-day {
            color: var(--ink);
        }

        [data-theme="dark"] .flatpickr-day.today {
            border-color: var(--accent);
        }

        [data-theme="dark"] .flatpickr-day:hover,
        [data-theme="dark"] .flatpickr-day:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .flatpickr-day.selected,
        [data-theme="dark"] .flatpickr-day.selected:hover,
        [data-theme="dark"] .flatpickr-day.selected:focus {
            background: var(--accent);
            border-color: var(--accent);
            color: #ffffff;
        }

        [data-theme="dark"] .flatpickr-day.prevMonthDay,
        [data-theme="dark"] .flatpickr-day.nextMonthDay,
        [data-theme="dark"] .flatpickr-day.flatpickr-disabled,
        [data-theme="dark"] .flatpickr-day.flatpickr-disabled:hover {
            color: #475569;
        }

    </style>

</head>
<body>

<div class="container">

    <div class="card main-card">

        <div class="card-header">
            <div class="header-title">
                <i class="bi bi-calendar-plus"></i> Jira Future Planner
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-dark btn-sm fw-bold me-2 theme-toggle" onclick="toggleTheme()" title="Switch to dark mode">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
                <button type="button" class="btn btn-dark btn-sm fw-bold me-2" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="bi bi-gear-fill"></i>
                </button>
                <a href="index.php" class="btn btn-light btn-sm fw-bold me-2">
                    <i class="bi bi-clock-history"></i> Daily Logger
                </a>
                <a href="task_list.php" class="btn btn-light btn-sm fw-bold">
                    <i class="bi bi-list-task"></i> Task List
                </a>
            </div>
        </div>

        <div class="card-body">

            <form id="futureForm">

                <div class="mb-4 p-4 rounded-3 target-date-box">
                    <label class="form-label text-primary">
                        <i class="bi bi-calendar-event"></i> Target Date for all tasks
                    </label>
                    <input
                        type="text"
                        class="form-control date-picker"
                        name="global_date"
                        placeholder="YYYY-MM-DD"
                        style="max-width: 250px; cursor: pointer; caret-color: transparent;"
                        required
                        autocomplete="off"
                        onkeydown="return false;"
                        onpaste="return false;"
                    >
                </div>

                <div id="taskContainer">

                    <div class="task-row">
                        <h6 class="task-number badge bg-secondary mb-3">Task #1</h6>

                        <div class="row">

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
                                    Time Estimate
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="time[]"
                                    placeholder="e.g: 1h or 20m"
                                    required
                                >

                            </div>

                            <!-- SECOND ROW (12 Cols) -->
                            <div class="col-md-4 mb-3">

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

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Team
                                </label>

                                <select
                                    class="form-select team-select"
                                    name="team[]"
                                >

                                    <option value="">
                                        Select
                                    </option>

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
                    <div class="alert alert-info d-none" id="settingsStatus"></div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="saveSettingsBtn">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>

// Initialize flatpickr on page load
flatpickr(".date-picker", {
    dateFormat: "Y-m-d",
    allowInput: true,
    disableMobile: true,
    onReady(_, __, fp) { fp.input.setAttribute("autocomplete", "off"); }
});

if (window.jQuery && $.fn.select2) {
    $('.form-select').select2({
        width: '100%'
    });
}

const container =
    document.getElementById('taskContainer');

function loadTeams() {
    fetch('api/fetch_teams.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.teams)) {
                return;
            }

            document.querySelectorAll('.team-select').forEach(select => {
                data.teams.forEach(team => {
                    const isDefault = team.name.trim().toLowerCase() === 'findev';
                    select.add(new Option(team.name, team.id, isDefault, isDefault));
                });
                if (window.jQuery && $.fn.select2) {
                    $(select).trigger('change.select2');
                }
            });
        })
        .catch(() => {});
}

loadTeams();

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
            allowInput: true,
            disableMobile: true,
            onReady(_, __, fp) { fp.input.setAttribute("autocomplete", "off"); }
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
                dueDate.value = e.target.value;
            }
        }
    }
});

document
    .getElementById('futureForm')
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
            'Planning Tasks...\n';

        const formData =
            new FormData(this);

        try {

            const response = await fetch(
                'api/process_future.php',
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
    
    const statusBox = document.getElementById('settingsStatus');
    const submitBtn = document.getElementById('saveSettingsBtn');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    statusBox.classList.add('d-none');
    
    try {
        const response = await fetch('api/save_settings.php', {
            method: 'POST',
            body: new FormData(this)
        });
        
        const result = await response.text();
        
        statusBox.classList.remove('d-none', 'alert-danger');
        statusBox.classList.add('alert-success');
        statusBox.innerHTML = result;
        
        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
            modal.hide();
            statusBox.classList.add('d-none');
        }, 1500);
        
    } catch (error) {
        statusBox.classList.remove('d-none', 'alert-success');
        statusBox.classList.add('alert-danger');
        statusBox.innerHTML = 'Error saving settings: ' + error.message;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Save Settings';
    }
});

</script>

</body>
</html>