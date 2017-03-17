<?php

class ZertifikatAddTable extends Migration
{
    public function description()
    {
        return 'Add DB table for Zertifikats-Plugin';
    }

    public function up()
    {
        $db = DBManager::get();

        // add db-table
        $db->exec("CREATE TABLE IF NOT EXISTS `zertifikat_config` (
            `course_id` varchar(32) NOT NULL PRIMARY KEY,
            `contact_mail` varchar(32) NOT NULL
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS `zertifikat_sent` (
            `user_id` varchar(32) NOT NULL,
            `course_id` varchar(32) NOT NULL,
            `mail_sent` tinyint(1) NOT NULL DEFAULT 0,
            `mkdate` int(20),
            PRIMARY KEY (`user_id`, `course_id`)
        )");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE zertifikat_config");
        $db->exec("DROP TABLE zertifikat_sent");

        SimpleORMap::expireTableScheme();
    }
}
