<!-- index.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jira Bulk Logger</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            background: #f3f4f6;
        }

        .main-card {
            max-width: 1400px;
            margin: 40px auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            background: #0d6efd;
            color: white;
            font-size: 24px;
            font-weight: bold;
            padding: 20px;
        }

        .task-row {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            background: white;
        }

        .response-box {
            background: #111827;
            color: #22c55e;
            min-height: 250px;
            border-radius: 10px;
            padding: 20px;
            font-family: monospace;
            white-space: pre-wrap;
        }

    </style>

</head>
<body>

<div class="container">

    <div class="card main-card">

        <div class="card-header">
            Jira Bulk Time Logger
        </div>

        <div class="card-body">

            <form id="jiraForm">

                <div id="taskContainer">

                    <div class="task-row">

                        <div class="row">

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
                                        HON
                                    </option>

                                    <option value="BANK">
                                        BANK
                                    </option>

                                    <option value="EMA">
                                        EMA
                                    </option>

                                </select>

                            </div>

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Task Title
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="title[]"
                                    placeholder="Fix login issue"
                                    required
                                >

                            </div>

                            <div class="col-md-1 mb-3">

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

                            <div class="col-md-2 mb-3">

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

                            <div class="col-md-2 mb-3">

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

                            <div class="col-md-2 mb-3">

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

                            <div class="col-md-2 mb-3">

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
                            class="btn btn-danger remove-btn"
                        >
                            Remove
                        </button>

                    </div>

                </div>

                <div class="d-flex gap-3">

                    <button
                        type="button"
                        id="addMore"
                        class="btn btn-success"
                    >
                        Add More
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Submit All Tasks
                    </button>

                </div>

            </form>

            <hr class="my-4">

            <h5>
                Response
            </h5>

            <div
                class="response-box"
                id="responseBox"
            >
Waiting for submission...
            </div>

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
    });

document.addEventListener('click', function(e) {

    if (
        e.target.classList.contains('remove-btn')
    ) {

        const rows =
            document.querySelectorAll('.task-row');

        if (rows.length > 1) {
            e.target.closest('.task-row').remove();
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