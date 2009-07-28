<?php

require('intl.php');
require('services.php');

// OPTIONS

$hookpress_value_options = 
  array('version' => $hookpress_version,
        'webhooks' => array()
        );

// BOOTSTRAP

/*update_option('hookpress_webhooks',array(
array(
  'url'=>'http://localhost:8888/test.php',
  'hook'=>'save_post',
  'enabled'=>true,
  'fields'=>array('ID','post_date','post_status','post_title')),
array(
  'url'=>'http://localhost:8888/test.php',
  'hook'=>'publish_post',
  'enabled'=>true,
  'fields'=>array('ID','post_date','post_status','post_title','post_type')),
array(
  'url'=>'http://localhost:8888/test.php',
  'hook'=>'add_category',
  'enabled'=>true,
  'fields'=>array('term_id','slug','taxonomy','description'))
));*/

// ACTION TYPES

$hookpress_actions = array(
  'add_attachment'=>array('ATTACHMENT'),
  'add_category'=>array('CATEGORY'),
  'clean_post_cache'=>array('POST'),
  'create_category'=>array('CATEGORY'),
  'delete_attachment'=>array('ATTACHMENT'),
  'delete_category'=>array('CATEGORY'),
  'delete_post'=>array('POST'),
  'deleted_post'=>array('POST'),
  'edit_attachment'=>array('ATTACHMENT'),
  'edit_category'=>array('CATEGORY'),
  'edit_post'=>array('POST'),
  'pre_post_update'=>array('POST'),
  'private_to_publish'=>array('POST'), // TODO: check if this is really the post ID
  'publish_page'=>array('POST'),
  'publish_phone'=>array('POST'),
  'publish_post'=>array('POST'),
  'save_post'=>array('POST'), // TODO: make sure the original post stuff is working
  'wp_insert_post'=>array('POST'),
  'xmlrpc_publish_post'=>array('POST'),

  'comment_closed'=>array('POST'),
  'comment_id_not_found'=>array('POST'),
  'comment_flood_trigger'=>array('time_lastcomment','time_newcomment'),
  'comment_on_draft'=>array('POST'),
  'comment_post'=>array('COMMENT','approval'),
  'edit_comment'=>array('COMMENT'),
  'delete_comment'=>array('COMMENT'),
  'pingback_post'=>array('COMMENT'),
  'pre_ping'=>array('COMMENT'),
  'traceback_post'=>array('COMMENT'),
  'wp_blacklist_check'=>array('comment_author','comment_author_email','comment_author_url','comment_content','comment_author_IP','comment_agent'),
  'wp_set_comment_status'=>array('COMMENT','status'),
  
  'add_link'=>array('LINK'),
  'delete_link'=>array('LINK'),
  'edit_link'=>array('LINK')

  // TODO: ADD MORE...
);

//'comment_post'

function hookpress_get_fields($type) {
  global $wpdb;
  $map = array('POST' => array($wpdb->posts),
               'COMMENT' => array($wpdb->comments),
               'CATEGORY' => array($wpdb->terms,$wpdb->term_taxonomy),
               'ATTACHMENT' => array($wpdb->posts));
  $tables = $map[$type];
  $fields = array();
  foreach ($tables as $table) {
    $fields = array_merge($fields,$wpdb->get_col("show columns in $table"));
  }
  return array_unique($fields);
}

// MAGIC

function hookpress_register_hooks() {
  global $hookpress_callbacks, $hookpress_actions;
  $hookpress_callbacks = array();
  
  foreach (get_option('hookpress_webhooks') as $id => $desc) {
    if (count($desc) && $desc['enabled']) {
      $hookpress_callbacks[$id] = create_function('','
        $args = func_get_args();
        hookpress_generic_action('.$id.',$args);
      ');
      add_action($desc['hook'], $hookpress_callbacks[$id], HOOKPRESS_PRIORITY, count($hookpress_actions[$desc['hook']]));
    }
  }
}

function hookpress_generic_action($id,$args) {
  global $hookpress_version, $wpdb, $hookpress_actions;
  
  $webhooks = get_option('hookpress_webhooks');
  $desc = $webhooks[$id];

  $obj = array();
  
  // generate the expected argument names
  $arg_names = $hookpress_actions[$desc['hook']];
  
  foreach($args as $i => $arg) {
    $newobj = array();
    switch($arg_names[$i]) {
      case 'POST':
      case 'ATTACHMENT':
      case 'LINK':
        $newobj = get_post($arg,ARRAY_A);
        if (wp_is_post_revision($arg)) {
          $parent = get_post(wp_is_post_revision($arg));
          foreach ($parent as $key => $val) {
            $newobj["parent_$key"] = $val;
          }
        }
        break;
      case 'COMMENT':
        $newobj = $wpdb->get_row("select * from $wpdb->comments where comment_ID = $arg",ARRAY_A);
        break;
      case 'CATEGORY':
        $newobj = $wpdb->get_row("select * from $wpdb->categories where cat_ID = $arg",ARRAY_A);
        break;
      default:
        $newobj[$arg_names[$i]] = $arg;
    }
    $obj = array_merge($obj,$newobj);
    
  }
  
  // take only the fields we care about
  $obj_to_post = array_intersect_key($obj,array_flip($desc['fields']));
  
  $obj_to_post['hook'] = $desc['hook'];
  
  $url = $desc['url'];
  include_once(ABSPATH . WPINC . '/class-snoopy.php');
  if (class_exists('Snoopy')) {
    $snoopy = new Snoopy;
    // TODO: add proxy settings... but is it really necessary?
    // $snoopy->proxy_host = "my.proxy.host";
    // $snoopy->proxy_port = "8080";
    // TODO: add authentication settings.
    // snoopy->user = "me";
    // $snoopy->pass = "p@ssw0rd";
    $snoopy->agent = "HookPress/$hookpress_version (compatible; WordPress ".$GLOBALS['wp_version']."; +http://mitcho.com/code/hookpress/)";
    $snoopy->referer = get_bloginfo('siteurl');
    $result = $snoopy->submit($url,$obj_to_post);
    if ($result) {
      return $snoopy->results;
    }
  }
}

function hookpress_print_webhook($id) {
  $webhooks = get_option('hookpress_webhooks');
  $desc = $webhooks[$id];
  $fields = implode('</code>, <code>',$desc['fields']);
  return "<tr id='$id'><td>".
  ($desc['enabled']?"<a href='#' id='on$id' style='font-size: 0.7em' class='on' title='click to turn off'>ON</a>":"<a href='#' id='off$id' style='font-size: 0.7em' class='off' title='click to turn on'>OFF</a>")
  ."</td><td><code><span style='font-weight: bold'>$desc[hook]</span></code></td><td><code>$desc[url]</code></td><td><code>"
  .$fields
  ."</code></td><td><!--<a href='#' id='edit$id' class='edit'>[edit]</a> --><a href='#' id='delete$id' class='delete'>[delete]</a></td></tr>";
}