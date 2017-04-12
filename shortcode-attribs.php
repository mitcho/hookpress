<?php
define("INCL",realpath(__DIR__ . "/../../../wp-includes"));

require INCL . "/shortcodes.php";

require "services.php";

$newobj = [];
$newobj["post_content"] = 'di daskda dasjkhdas [audio ogg="http://localhost:9000/wp-content/uploads/2017/02/316829__lalks__ferambie.ogg" teste="abc" xxx][/audio] [biskit kkk="isisis"]dasdasd';

$ex = hookpress_get_shortcode_attribs($newobj["post_content"],'audio');
$ex = hookpress_parse_attribs( $ex );

print_r($ex);
echo "\n";