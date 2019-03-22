<?php
defined('mnminclude') or die();

$query = '
    FROM posts, conversations
    WHERE (
        conversation_user_to = "'.(int)$user->id.'"
        AND conversation_type = "post"
        AND post_id = conversation_from
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
        ORDER BY '.$order.'
        LIMIT '.$offset.', '.$limit.'
    ) AS `id`
    USING (post_id);
', 'Post');

require __DIR__ . '/notes-common.php';

if ($time_read > 0 && $user->id == $current_user->user_id) {
    Post::update_read_conversation($time_read);
}
