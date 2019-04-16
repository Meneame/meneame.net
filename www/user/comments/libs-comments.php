<?php
defined('mnminclude') or die();

function print_comment_list($comments, $user)
{
    global $globals, $current_user;

    $timestamp_read = 0;
    $last_link = 0;
    $ids = $links = [];

    function isValid ($comment, $user, $current_user) {
        return $current_user->admin
            || ($comment->comment_type !== 'admin')
            || ($user->id != $comment->comment_user_id);
    }

    foreach ($comments as $comment) {
        if (isValid($comment, $user, $current_user)) {
            $ids[] = $comment->comment_id;
            $links[] = $comment->comment_link_id;
        }
    }

    $comments = Comment::from_ids($ids);
    $links = DbHelper::keyBy(Link::from_ids(array_unique($links)), 'id');

    foreach ($comments as $comment) {
        if (!($link = $links[$comment->link])) {
            continue;
        }

        if ($last_link != $comment->link) {
            echo '<h4>';
            echo '<a href="' . $link->get_permalink() . '">' . $link->title . '</a>';
            echo ' [' . $link->comments . ']';
            echo '</h4>';

            $last_link = $link->id;
        }

        if ($comment->date > $timestamp_read) {
            $timestamp_read = $comment->date;
        }

        echo '<ol class="comments-list">';
        echo '<li>';

        $comment->link_object = $link;
        $comment->print_summary(2000, false);

        echo '</li>';
        echo "</ol>\n";

        $ids[] = $comment->id;
    }

    Haanga::Load('get_total_answers_by_ids.html', array(
        'type' => 'comment',
        'ids' => implode(',', $ids),
    ));

    // Return the timestamp of the most recent comment
    return $timestamp_read;
}