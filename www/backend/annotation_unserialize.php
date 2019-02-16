<?php
require_once __DIR__.'/../config.php';

$id = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $_GET['id']);
$annotation = new Annotation($id);

echo '<div style="text-align: left">';

if ($annotation->read()) {
    $array = (array)unserialize($annotation->text);
    $array = array_intersect_key($array, array_flip(['title', 'tags', 'uri', 'content', 'status', 'url', 'sub_id']));

    echo '<strong style="font-variant: small-caps">'._('modificación').':</strong> '.get_date_time($annotation->time);
    echo '<ul>';

    foreach ($array as $k => $v) {
        echo "<li><strong style='font-variant: small-caps'>$k</strong>: $v</li>\n";
    }

    echo '</ul>';
} else {
    echo _('objeto inexistente').': ', __($id);
}

echo '</div>';
