<?php
    requirePermission("veeam.view");
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h4>VEEAM Backup Dashboard</h4>
            <p>Monitor and manage your VEEAM backup jobs.</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Jobs</h5>
                    <h2 id="total-jobs">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Successful</h5>
                    <h2 id="successful-jobs" class="text-success">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Warning</h5>
                    <h2 id="warning-jobs" class="text-warning">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Failed</h5>
                    <h2 id="failed-jobs" class="text-danger">-</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Backup History (Last 30 Days)</h5>
                    <div class="table-responsive">
                        <table id="backup-table" class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Job Name</th>
                                    <th>Last Run</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th>Processed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobDetailsContent">
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadBackupHistory();

    function loadBackupHistory() {
        queryAPI("GET", "/api/plugin/veeam/backups").done(function(data) {
            if (data.result === "Success" && data.data) {
                updateDashboardStats(data.data);
                populateBackupTable(data.data.Jobs);
            }
        });
    }

    function updateDashboardStats(data) {
        var stats = {
            total: 0,
            success: 0,
            warning: 0,
            failed: 0
        };

        if (data.Jobs) {
            data.Jobs.forEach(function(job) {
                stats.total++;
                if (job.Status === "Success") stats.success++;
                else if (job.Status === "Warning") stats.warning++;
                else if (job.Status === "Failed") stats.failed++;
            });
        }

        $("#total-jobs").text(stats.total);
        $("#successful-jobs").text(stats.success);
        $("#warning-jobs").text(stats.warning);
        $("#failed-jobs").text(stats.failed);
    }

    function populateBackupTable(jobs) {
        var table = $("#backup-table").bootstrapTable({
            data: jobs,
            pagination: true,
            pageSize: 10,
            sortName: "LastRun",
            sortOrder: "desc",
            columns: [{
                field: "Name",
                title: "Job Name"
            }, {
                field: "LastRun",
                title: "Last Run",
                formatter: function(value) {
                    return new Date(value).toLocaleString();
                }
            }, {
                field: "Status",
                title: "Status",
                formatter: function(value) {
                    var statusClass = value === "Success" ? "text-success" : 
                                    (value === "Warning" ? "text-warning" : "text-danger");
                    return '<span class="' + statusClass + '">' + value + '</span>';
                }
            }, {
                field: "Duration",
                title: "Duration"
            }, {
                field: "ProcessedSize",
                title: "Processed",
                formatter: function(value) {
                    return formatBytes(value);
                }
            }, {
                field: "Id",
                title: "Actions",
                formatter: function(value) {
                    return '<button class="btn btn-sm btn-primary view-details" data-job-id="' + value + '">Details</button>';
                }
            }]
        });
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    $(document).on('click', '.view-details', function() {
        var jobId = $(this).data('job-id');
        queryAPI("GET", "/api/plugin/veeam/jobs/" + jobId).done(function(data) {
            if (data.result === "Success" && data.data) {
                showJobDetails(data.data);
            }
        });
    });

    function showJobDetails(job) {
        var detailsHtml = '<div class="container-fluid">';
        detailsHtml += '<div class="row"><div class="col"><h6>Job Information</h6></div></div>';
        detailsHtml += '<div class="row">';
        detailsHtml += '<div class="col-md-6"><strong>Name:</strong> ' + job.Name + '</div>';
        detailsHtml += '<div class="col-md-6"><strong>Type:</strong> ' + job.Type + '</div>';
        detailsHtml += '</div>';
        // Add more job details as needed
        detailsHtml += '</div>';

        $('#jobDetailsContent').html(detailsHtml);
        $('#jobDetailsModal').modal('show');
    }
});
</script>
