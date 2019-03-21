<?php
defined('mnminclude') or die();

$query = '
    FROM posts, friends
    WHERE (
        friend_type = "manual"
        AND friend_from = "'.(int)$user->id.'"
        AND friend_to = post_user_id
        AND friend_value > 0
    )
';

$count = $db->get_var('SELECT SQL_CACHE COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$sort = empty($_GET['sort']) ? 'date' : $_GET['sort'];

if ($sort === 'votes') {
    $order = 'post_votes DESC';
} elseif ($sort === 'karma') {
    $order = 'post_karma DESC';
} else {
    $order = 'post_id DESC';
}

$posts = $db->get_results('
    SELECT '.Post::SQL.'
    INNER JOIN (
        SELECT post_id
        '.$query.'
        ORDER BY post_id DESC
        LIMIT '.$offset.', '.$limit.'
    ) AS `id`
    USING (post_id)
    ORDER BY '.$order.';
', 'Post');

require __DIR__ . '/notes-common.php';
