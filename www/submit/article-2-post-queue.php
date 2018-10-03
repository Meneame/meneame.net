<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

// Store previous value for the log
$link_old = $link->duplicate();

if ($link->status === 'private') {
    $link->sub_id = 0;
} elseif ((int)$_POST['sub_id']) {
    $link->sub_id = (int)$_POST['sub_id'];
}

if ($link->sub_id != $link_old->sub_id) {
    $link->sub_changed = true; // To force to delete old statuses with another origin
}

$link->title = $_POST['title'];
$link->content = $_POST['bodytext'];

if ($error = $link->check_field_errors()) {
    return addFormError($error);
}

try {
    $validator->checkSiteSend();
} catch (Exception $e) {
    return;
}

// change the status
if (
    $_POST['status'] !== $link->status
    && (($_POST['status'] === 'autodiscard') || $current_user->admin || SitesMgr::is_owner())
    && preg_match('/^[a-z]{4,}$/', $_POST['status'])
    && (!$link->is_discarded() || $current_user->admin || SitesMgr::is_owner())
) {
    if (preg_match('/discard|abuse|duplicated|autodiscard/', $_POST['status'])) {
        // Insert a log entry if the link has been manually discarded
        $insert_discard_log = true;
    }

    $link->status = $_POST['status'];
}

$link->title = $link->get_title_fixed();
$link->content = $link->get_content_fixed();

Backup::store('links', $link->id, $link_old);

$db->transaction();

if (($link->author == $current_user->user_id) || $current_user->admin) {
    $link->store();
}

if ($insert_discard_log) {
    // Insert always a link and discard event if the status has been changed to discard
    Log::insert('link_discard', $link->id, $current_user->user_id);

    // Don't save edit log if it's discarded by an admin
    if (($link->author == $current_user->user_id && $link->votes == 0) || $current_user->admin) {
        Log::insert('link_edit', $link->id, $current_user->user_id);
    }
} elseif ($link->votes > 0) {
    $link_old = array_intersect_key((array)$link_old, array_flip(['title', 'tags', 'uri', 'content', 'status', 'url', 'sub_id']));

    Log::conditional_insert('link_edit', $link->id, $current_user->user_id, 60, serialize($link_old));
}

$db->commit();

die(header('Location: '.$link->get_permalink()));
