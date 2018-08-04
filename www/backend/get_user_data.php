<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=utf-8');

if (empty($current_user->user_id)) {
    do_error(_('usuario inexistente'), 404);
}

if ($globals['memcache_host']) {
    $memcache_key = basename(__FILE__).'-'.$current_user->user_id;

    if ($memcache_value = memcache_mget($memcache_key)) {
        header('Content-disposition: attachment; filename=user-data.json');
        header('Content-type: application/json');

        die($memcache_value);
    }
}

function getMedia($row)
{
    if ($row['media_id']) {
        return Upload::get_url($row['media_type'], $row['media_id']);
    }
}

// MySQLi functions execution to optimized CPU and Memory usage

$data = [
    'profile' => [
        'id' => $current_user->user_id,
        'username' => $current_user->user_login,
        'date' => date('Y-m-d H:i:s', $current_user->user_date),
        'email' => $current_user->user_email,
    ],
    'links' => [],
    'comments' => [],
    'posts' => []
];

$base = $globals['scheme'].'//'.$globals['server_name'].$globals['base_url_general'];

$db->real_query('
    SELECT SQL_CACHE
        `link_id`, `link_title`, `link_karma`, `link_votes`, `link_negatives`, `link_anonymous`,
        `link_comments`, `link_date`, `link_published_date`, `link_content_type`, `link_content`,
        `link_url`, `link_uri`, CONCAT("'.$base.'story/'.'", `link_uri`) AS `link_permalink`
    FROM `links`
    WHERE `link_author` = "'.(int)$current_user->user_id.'"
    ORDER BY `link_id` ASC;
');

$query = $db->store_result();

while ($row = $query->fetch_assoc()) {
    $data['links'][] = $row;
}

$query->free();

$db->real_query('
    SELECT SQL_CACHE
        `comment_id`, `comment_date`, `comment_content`, `comment_order`, `comment_karma`, `comment_votes`,
        CONCAT("'.$base.'c/'.'", `comment_id`) AS `comment_permalink`,
        `link_title`, CONCAT("'.$base.'story/'.'", `link_uri`) AS `link_permalink`,
        `media`.`id` AS `media_id`, `media`.`type` AS `media_type`, `media`.`user` AS `media_user`
    FROM (`comments`, `links`)
    LEFT JOIN `media` ON (
        `media`.`type` = "comment"
        AND `media`.`id` = `comment_id`
        AND `media`.`version` = 0
        AND `media`.`access` = "restricted"
    )
    WHERE (
        `comment_user_id` = "'.(int)$current_user->user_id.'"
        AND `link_id` = `comment_link_id`
    )
    ORDER BY `comment_id` ASC;
');

$query = $db->store_result();

while ($row = $query->fetch_assoc()) {
    $row['media_permalink'] = getMedia($row);

    unset($row['media_id'], $row['media_type'], $row['media_user']);

    $data['comments'][] = $row;
}

$query->free();

$db->real_query('
    SELECT SQL_CACHE
        `post_id`, `post_date`, `post_content`, `post_karma`, `post_votes`,
        CONCAT("'.$base.'notame/'.'", `post_id`) AS `post_permalink`,
        `media`.`id` AS `media_id`, `media`.`type` AS `media_type`, `media`.`user` AS `media_user`
    FROM `posts`
    LEFT JOIN `media` ON (
        `media`.`type` = "post"
        AND `media`.`id` = `post_id`
        AND `media`.`version` = 0
        AND `media`.`access` = "restricted"
    )
    WHERE `post_user_id` = "'.(int)$current_user->user_id.'"
    ORDER BY `post_id` ASC;
');

$query = $db->store_result();

while ($row = $query->fetch_assoc()) {
    $row['media_permalink'] = getMedia($row);

    unset($row['media_id'], $row['media_type'], $row['media_user']);

    $data['comments'][] = $row;
}

$query->free();

$data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_PARTIAL_OUTPUT_ON_ERROR);

if ($globals['memcache_host']) {
    memcache_madd($memcache_key, $data, 1800);
}

header('Content-disposition: attachment; filename=user-data.json');
header('Content-type: application/json');

die($data);
