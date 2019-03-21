<?php
defined('mnminclude') or die();

$query = '
    FROM links, favorites
    WHERE (
        favorite_user_id = "'.(int)$user->id.'"
        AND favorite_type = "link"
        AND link_content_type = "article"
        AND favorite_link_id = link_id
    )
';

$count = (int)$db->get_var('SELECT SQL_CACHE COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$sort = empty($_GET['sort']) ? 'date' : $_GET['sort'];

if ($sort === 'votes') {
    $order = 'link_votes DESC';
} elseif ($sort === 'karma') {
    $order = 'link_karma DESC';
} else {
    $order = 'link_id DESC';
}

$links = $db->get_col('
    SELECT SQL_CACHE link_id
    '.$query.'
    ORDER BY favorite_link_readed ASC, '.$order.'
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

Haanga::Load('user/sort_header.html', [
    'sort' => $sort
]);

foreach ($links as $link_id) {
    Link::from_db($link_id)->print_summary('short', 0, false, 'link_summary_favorites.html', '', $user);
}

do_pages($count, $limit);
