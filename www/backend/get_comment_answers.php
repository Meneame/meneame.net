<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at gmail dot com>and Menéame Comunicacions
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Use the alternate server for api, if it exists
//$globals['alternate_db_server'] = 'backend';

require_once __DIR__.'/../config.php';

$id = intval($_GET['id']) or die();

$ids = $db->get_col('
    SELECT conversation_from comment_id
    FROM conversations, comments
    WHERE (
        conversation_type = "comment"
        AND conversation_to = "'.$id.'"
        AND comment_id = conversation_from
    )
    ORDER BY conversation_from ASC;
');

$ids or die();

$strikes = Strike::getCommentsIdsByIds($ids);

header('Content-Type: text/html; charset=UTF-8');

foreach ($ids as $id) {
    $comment = Comment::from_db($id);

    $comment->basic_summary = true;
    $comment->not_ignored = true;
    $comment->prefix_id = "$id-";
    $comment->setStrikeByIds($strikes);
    $comment->print_summary(false, 2500);

    echo "\n";
}
