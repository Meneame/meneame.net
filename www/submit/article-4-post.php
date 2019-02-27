<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

try {
    $validator->checkKey();
} catch (Exception $e) {
    return;
}

// Store previous value for the log
Backup::store('links', $link->id, $link->duplicate());

if ($_POST['title'] = trim($_POST['title'])) {
    $link->title = $_POST['title'];
}

if ($_POST['uri'] = trim($_POST['uri'])) {
    $link->uri = $_POST['uri'];
}

$link->nsfw = !empty($_POST['nsfw']);

$link->store_advanced();

die(header('Location: '. $link->get_permalink()));
