<?php
defined('mnminclude') or die();

$query = '
    FROM comments, favorites
    WHERE (
        favorite_user_id = "'.(int)$user->id.'"
        AND favorite_type = "comment"
        AND favorite_link_id = comment_id
    )
';

$count = (int)$db->get_var('SELECT SQL_CACHE COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$sort = empty($_GET['sort']) ? 'date' : $_GET['sort'];

if ($sort === 'votes') {
    $order = 'comment_votes DESC';
} elseif ($sort === 'karma') {
    $order = 'comment_karma DESC';
} else {
    $order = 'comment_id DESC';
}

$comments = $db->get_results('
    SELECT SQL_CACHE comment_id, comment_link_id, comment_type, comment_user_id
    '.$query.'
    ORDER BY '.$order.'
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($comments)) {
    return Haanga::Load('user/empty.html');
}

Haanga::Load('user/sort_header.html', [
    'sort' => $sort
]);

require __DIR__.'/libs-comments.php';

print_comment_list($comments, $user);

do_pages($count, $limit);
