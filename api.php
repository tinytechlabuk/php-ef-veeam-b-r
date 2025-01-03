<?php

$app->group('/plugin/veeam', function() use ($app) {
    
    // Get backup history for the last month
    $app->get('/backups', function($request, $response, $args) {
        $veeam = new veeamPlugin($this);
        return $veeam->GetBackupHistory();
    });

    // Get specific job details
    $app->get('/jobs/{id}', function($request, $response, $args) {
        $veeam = new veeamPlugin($this);
        return $veeam->GetJobDetails($args['id']);
    });
});
