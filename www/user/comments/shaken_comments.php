<?php
defined('mnminclude') or die();

$query = '
    FROM votes, comments
    WHERE (
        vote_type = "comments"
        AND vote_user_id = "'.(int)$user->id.'"
        AND comment_id = vote_link_id
        AND comment_user_id != vote_user_id
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
    $order = 'vote_id DESC';
}

$comments = DbHelper::keyBy($db->get_results('
    SELECT SQL_CACHE vote_link_id AS id, vote_value AS value
    '.$query.'
    ORDER BY '.$order.'
    LIMIT '.(int)$offset.', '.(int)$limit.';
'), 'id');

if (empty($comments)) {
    return;
}

Haanga::Load('user/sort_header.html', [
    'sort' => $sort
]);

echo '<ol class="comments-list">';

foreach (Comment::from_ids(array_keys($comments)) as $comment) {
    if ($comment->author == $user->id || $comment->admin) {
        continue;
    }

    echo '<li style="position: relative">';

    $comment->print_summary(1000, false);

    $color = ($comments[$comment->id]->value > 0) ? 'green' : 'red';

    echo '<div class="box" style="background: ' . $color . '; position: absolute; bottom: 0; right: 0; width: 29px; height: 19px; opacity: 0.5"></div>';
    echo '</li>';
}

echo "</ol>\n";

do_pages($count, $limit);
