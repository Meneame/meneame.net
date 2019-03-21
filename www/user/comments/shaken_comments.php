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

$comments = $db->get_results('
    SELECT SQL_CACHE vote_link_id AS id, vote_value AS value
    '.$query.'
    ORDER BY '.$order.'
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($comments)) {
    return;
}

Haanga::Load('user/sort_header.html', [
    'sort' => $sort
]);

echo '<ol class="comments-list">';

foreach ($comments as $c) {
    $comment = Comment::from_db($c->id);

    if ($comment->author == $user->id || $comment->admin) {
        continue;
    }

    $color = ($c->value > 0) ? 'green' : 'red';

    echo '<li>';

    $comment->print_summary(1000, false);

    echo '<div class="box" style="background: ' . $color . '; position: relative; top: -30px; left: 300px; width: 29px; height: 19px; opacity: 0.5"></div>';
    echo '</li>';
}

echo "</ol>\n";

do_pages($count, $limit);
