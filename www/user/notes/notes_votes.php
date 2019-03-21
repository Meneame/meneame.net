<?php
defined('mnminclude') or die();

$query = '
    FROM posts, votes
    WHERE (
        vote_user_id = "'.(int)$user->id.'"
        AND vote_type = "posts"
        AND post_id = vote_link_id
        AND post_user_id != vote_user_id
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
    $order = 'vote_id DESC';
}

$posts = $db->get_results('
    SELECT SQL_CACHE vote_link_id AS id, vote_value AS value
    '.$query.'
    ORDER BY '.$order.'
    LIMIT '.$offset.', '.$limit.';
', 'Post');

require __DIR__ . '/notes-common.php';
