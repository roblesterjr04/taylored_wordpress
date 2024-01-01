<?php

require_once __DIR__ . '../../log/write_to_log_file.php';
require_once __DIR__ . '/get_jobs.php';
require_once __DIR__ . '/update_job.php';
require_once __DIR__ . '/job_do_cmd.php';
require_once __DIR__ . '/process_jobs.php';

// add ajax handler
add_action('wp_ajax_cronjob_do_cmd', 'job_do_cmd');

// render html for page
function render_ci_store_plugin_jobs()
{
    ?>
    <div id='ci-store-plugin-container-jobs'></div>
    <script>
        document.addEventListener("DOMContentLoaded", () => CIStore.render('ci-store-plugin-container-jobs', 'jobs'));
    </script>
    <?php
}

// add submenu item to side nav
function admin_menu_cistore_jobs()
{
    add_submenu_page('ci-store-plugin-page', 'Jobs', 'Jobs', 'manage_options', 'ci-store-plugin-page-jobs', 'render_ci_store_plugin_jobs');
}

// need a lower priority so it executes after the main nav item is added
add_action('admin_menu', 'admin_menu_cistore_jobs', 12);

// initialize schedule a check to run periodically
function schedule_job_update()
{
    $next = wp_next_scheduled('schedule_job_event');
    if ($next === false) {
        write_to_log_file("schedule_job_update() next=" . $next . " " . date('c', $next));
        wp_schedule_event(time(), 'every_minute', 'ci_handle_schedule_event');
    }
}

add_action('wp', 'schedule_job_update');

// periodic check triggers this function
add_action('ci_handle_schedule_event', 'process_jobs');