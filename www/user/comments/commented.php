<?php
defined('mnminclude') or die();

$query = '
    FROM comments, links
    WHERE (
        comment_user_id = "'.(int)$user->id.'"
        AND link_id = comment_link_id
    )
';

$sort = empty($_GET['sort']) ? 'date' : $_GET['sort'];

if ($sort === 'votes') {
    $order = 'comment_votes DESC';
} elseif ($sort === 'karma') {
    $order = 'comment_karma DESC';
} else {
    $order = 'comment_id DESC';
}

$comments = $db->get_results('
    SELECT SQL_CACHE comment_id, link_id, comment_type
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

do_pages(-1, $limit);