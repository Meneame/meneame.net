<?php
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//        http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Backup
{
    public static function store($table, $id, $contents)
    {
        global $globals, $db, $current_user;

        $db->query('
            INSERT INTO `backups`
            SET
                `related_table` = "'.$db->escape($table).'",
                `related_id` = "'.(int)$id.'",
                `contents` = "'.static::contents($contents).'",
                `ip` = "'.$globals['user_ip'].'",
                `user_id` = "'.(int)$current_user->user_id.'";
        ');
    }

    protected static function contents($contents)
    {
        global $db;

        if (is_array($contents) || is_object($contents)) {
            $contents = json_encode($contents);
        }

        return $db->escape($contents);
    }
}
