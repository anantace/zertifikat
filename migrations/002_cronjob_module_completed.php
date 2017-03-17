<?php

/**
 * 020_cronjob_preliminary_participants.php
 *
 * @author Till Glöggler <tgloeggl@uos.de>
 */
class CronjobModuleCompleted extends Migration
{

    const FILENAME = 'public/plugins_packages/elan-ev/Zertifikats_Plugin/cronjobs/module_completed.php';

    public function description()
    {
        return 'add cronjob for sending certificate after completion of module';
    }

    public function up()
    {
        $task_id = CronjobScheduler::registerTask(self::FILENAME, true);

        // Schedule job to run every day at 23:59
        if ($task_id) {
            CronjobScheduler::schedulePeriodic($task_id, -1);  // negative value means "every x minutes"
        }
    }

    function down()
    {
        if ($task_id = CronjobTask::findByFilename(self::FILENAME)->task_id) {
            CronjobScheduler::unregisterTask($task_id);
        }
    }
}
