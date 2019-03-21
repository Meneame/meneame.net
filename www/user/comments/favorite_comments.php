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

$comments = $db->get_col('
    SELECT SQL_CACHE comment_id
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

echo '<ol class="comments-list">';

foreach ($comments as $comment_id) {
    echo '<li>';
    Comment::from_db($comment_id)->print_summary(2000, false);
    echo '</li>';
}

echo "</ol>\n";

do_pages($count, $limit);
