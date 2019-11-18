<?php

namespace block_jobonline\task;

class general_cron extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('general_cron', 'block_jobonline');
    }

    public function execute()
    {
        require_once(__DIR__.'/../../block_jobonline.php');
        \block_jobonline::readfeed();
    }
}
