<?php

require('intl.php');
require('services.php');
require('hooks.php');

// OPTIONS

$hookpress_value_options = 
  array('version' => $hookpress_version,
        'webhooks' => array()
        );

function hookpress_get_fields($type) {
  global $wpdb;
  $map = array('POST' => array($wpdb->posts),
               'PARENT_POST' => array($wpdb->posts),
               'COMMENT' => array($wpdb->comments),
               'CATEGORY' => array($wpdb->terms,$wpdb->term_taxonomy),
               'ATTACHMENT' => array($wpdb->posts),
               'LINK' => array($wpdb->links),
               'USER' => array($wpdb->users),
               'TAG_OBJ' => array($wpdb->terms,$wpdb->term_taxonomy),
               'USER_OBJ' => array($wpdb->users),
               'OLD_USER_OBJ' => array($wpdb->users));
  $tables = $map[$type];
  $fields = array();
  foreach ($tables as $table) {
    if (is_array($table))
      $fields = array_merge($fields,$table);
    else
      $fields = array_merge($fields,$wpdb->get_col("show columns in $table"));
  }

  // if it's a POST, we have a URL for it as well.
  if ($type == 'POST' || $type == 'PARENT_POST')
    $fields[] = 'post_url';

  if ($type == 'PARENT_POST')
    $fields = array_map(create_function('$x','return "parent_$x";'),$fields);

  if ($type == 'OLD_USER_OBJ')
    $fields = array_map(create_function('$x','return "old_$x";'),$fields);

  return array_unique($fields);
}

function hookpress_print_webhook($id) {
  $webhooks = get_option('hookpress_webhooks');
  $desc = $webhooks[$id];
  $fields = implode('</code>, <code>',$desc['fields']);
  if (!isset($desc['type']))
    $desc['type'] = 'action';
  return "<tr id='$id'>"
  ."<td>"
  .( $desc['enabled']
    ? "<a href='#' id='on$id' style='font-size: 0.7em' class='on' title='"
      .__('click to turn off',"hookpress").")'>".__('ON',"hookpress")."</a>"
    : "<a href='#' id='off$id' style='font-size: 0.7em' class='off' title='"
      .__('click to turn on',"hookpress")."'>".__('OFF',"hookpress")."</a>" )
  ."</td>"
  ."<td>"
    .($desc['type'] == 'filter'?__('filter','hookpress'):__('action','hookpress'))
  .":</td>"
  ."<td><code><span style='font-weight: bold'>$desc[hook]</span></code></td>"
  ."<td><code>$desc[url]</code></td>"
  ."<td><code ".($desc['type'] == 'filter' ? " style='background-color:#ECEC9D' title='".__('The data in the highlighted field is expected to be returned from the webhook, with modification.','hookpress')."'":"").">".$fields."</code></td>"
  ."<td><!-- style='width:7em'--><!--<a class='thickbox edit' title='Edit webhook' href='#TB_inline?inlineId=hookpress-new-webhook&height=330&width=500' id='edit$id'>[edit]</a> --><a href='#' id='delete$id' class='delete'>[".__('delete','hookpress')."]</a></td></tr>";
}

function hookpress_check_version_json($version) {
  include_once(ABSPATH . WPINC . '/class-snoopy.php');
  if (class_exists('Snoopy')) {
    $snoopy = new Snoopy;
    $snoopy->referer = get_bloginfo('siteurl');
    $result = $snoopy->fetch("http://mitcho.com/code/hookpress/checkversion.php?version=$version");
    if ($result) {
      return $snoopy->results;
    }
  }
  return '{}';
}

// MAGIC

function hookpress_obj_to_array($object) {
  $array = array();
  foreach($object as $member=>$data)
    $array[$member] = $data;
  return $array;
}

function hookpress_register_hooks() {
  global $hookpress_callbacks, $hookpress_actions, $hookpress_filters;
  $hookpress_callbacks = array();
  
  if (!is_array(get_option('hookpress_webhooks')))
    return;
  foreach (get_option('hookpress_webhooks') as $id => $desc) {
    if (count($desc) && $desc['enabled']) {
      $hookpress_callbacks[$id] = create_function('','
        $args = func_get_args();
        return hookpress_generic_action('.$id.',$args);
      ');
      if (isset($desc['type']) && $desc['type'] == 'filter')
        add_filter($desc['hook'], $hookpress_callbacks[$id], HOOKPRESS_PRIORITY, count($hookpress_filters[$desc['hook']]));
      else
        add_action($desc['hook'], $hookpress_callbacks[$id], HOOKPRESS_PRIORITY, count($hookpress_actions[$desc['hook']]));
    }
  }
}

function hookpress_generic_action($id,$args) {
  global $hookpress_version, $wpdb, $hookpress_actions, $hookpress_filters;
  
  $webhooks = get_option('hookpress_webhooks');
  $desc = $webhooks[$id];

  $obj = array();
  
  // generate the expected argument names
  if (isset($desc['type']) && $desc['type'] == 'filter')
    $arg_names = $hookpress_filters[$desc['hook']];  
  else
    $arg_names = $hookpress_actions[$desc['hook']];
  
  foreach($args as $i => $arg) {
    $newobj = array();
    switch($arg_names[$i]) {
      case 'POST':
      case 'ATTACHMENT':
        $newobj = get_post($arg,ARRAY_A);

        if ($arg_names[$i] == 'POST')
          $newobj["post_url"] = get_permalink($newobj["ID"]);
          
        if (wp_is_post_revision($arg)) {
          $parent = get_post(wp_is_post_revision($arg));
          foreach ($parent as $key => $val) {
            $newobj["parent_$key"] = $val;
          }
          $newobj["parent_post_url"] = get_permalink($newobj["parent_ID"]);
        }
        
        break;
      case 'COMMENT':
        $newobj = $wpdb->get_row("select * from $wpdb->comments where comment_ID = $arg",ARRAY_A);
        break;
      case 'CATEGORY':
        $newobj = $wpdb->get_row("select * from $wpdb->categories where cat_ID = $arg",ARRAY_A);
        break;
      case 'USER':
        $newobj = $wpdb->get_row("select * from $wpdb->users where ID = $arg",ARRAY_A);
        break;
      case 'LINK':
        $newobj = $wpdb->get_row("select * from $wpdb->links where link_id = $arg",ARRAY_A);
        break;
      case 'TAG_OBJ':
        $newobj = hookpress_obj_to_array($arg);
        break;
      case 'USER_OBJ':
        $newobj = hookpress_obj_to_array($arg);
      case 'OLD_USER_OBJ':
        $newobj = array_map(create_function('$x','return "old_$x";'),hookpress_obj_to_array($arg));
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
    // $snoopy->user = "me";
    // $snoopy->pass = "p@ssw0rd";
    $snoopy->maxredirs = 0;
    $snoopy->agent = "HookPress/$hookpress_version (compatible; WordPress ".$GLOBALS['wp_version']."; +http://mitcho.com/code/hookpress/)";
    $snoopy->referer = get_bloginfo('siteurl');
    $result = $snoopy->submit($url,$obj_to_post);
    if ($result) {
      return $snoopy->results;
    }
  }
}
