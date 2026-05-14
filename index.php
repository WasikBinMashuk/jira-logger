<!-- index.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jira Bulk Logger</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>

        body {
            background: linear-gradient(135deg, #f6f8fb 0%, #e5ebf4 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .main-card {
            max-width: 1400px;
            margin: 40px auto;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: #ffffff;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            font-size: 26px;
            font-weight: 700;
            padding: 25px 30px;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-body {
            padding: 40px 30px;
        }

        .task-row {
            border: 1px solid #eef0f3;
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 25px;
            background: #fafbfc;
            transition: all 0.3s ease;
            position: relative;
        }

        .task-row:hover {
            border-color: #dce0e5;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            background: #ffffff;
        }

        .task-number {
            font-size: 0.85rem;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
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
            background: #e6f4ea;
            color: #0f5132;
            border: none;
        }

        .btn-success:hover {
            background: #c3e6cb;
            color: #0f5132;
        }

        .btn-primary {
            background: #0d6efd;
            border: none;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }

        .btn-primary:hover {
            background: #0b5ed7;
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
            transform: translateY(-1px);
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
            <i class="bi bi-jira"></i> Jira Work Logger
        </div>

        <div class="card-body">

            <form id="jiraForm">

                <div id="taskContainer">

                    <div class="task-row">
                        <h6 class="task-number badge bg-secondary mb-3">Task #1</h6>

                        <div class="row">

                            <!-- FIRST ROW (12 Cols) -->
                            <div class="col-md-2 mb-3">

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

                                    <option value="HON">
                                        HONDA
                                    </option>

                                    <option value="BANK">
                                        BANK CRM
                                    </option>

                                    <option value="EMA">
                                        Easy Merchant App
                                    </option>

                                </select>

                            </div>

                            <div class="col-md-5 mb-3">

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

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Start Date
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    name="start_date[]"
                                    required
                                >

                            </div>

                            <!-- SECOND ROW (12 Cols) -->
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

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Due Date
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    name="due_date[]"
                                    required
                                >

                            </div>

                            <div class="col-md-3 mb-3">

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

<script>

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
        });

        clone.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });

        container.appendChild(clone);
        updateTaskNumbers();
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
    .getElementById('jiraForm')
    .addEventListener('submit', async function(e) {

        e.preventDefault();

        const responseBox =
            document.getElementById('responseBox');

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

        } catch (error) {

            responseBox.innerHTML =
                error.message;
        }
    });

</script>

</body>
</html>