<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

defined('mnminclude') or die();

if (!$current_user->admin || empty($link->id)) {
    die();
}

if ($_POST) {
    require __DIR__.'/article-4-post.php';
}

do_header(_('Edición avanzada de artículo') . ' 4/4', _('Edición avanzada de artículo'));

$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());

Haanga::Load('story/submit/article-4.html', array(
    'link' => $link
));
