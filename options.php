<script type='text/javascript'>

var getHooks = function getHooks() {
  var type = jQuery('.newtype:checked').attr('id');
  if (type == 'action')
    jQuery('#action_or_filter').text('<?php _e("Action:",'hookpress');?> ');
  if (type == 'filter')
    jQuery('#action_or_filter').text('<?php _e("Filter:",'hookpress');?> ');
	jQuery.ajax({type:'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_get_hooks&type='+type,
    beforeSend:function(){jQuery('#newhook').html('<img src="../wp-content/plugins/hookpress/i/spin.gif" alt="loading..."/>')},
    success:function(html){
      jQuery('#newhook').html(html);
      getFields();
    },
    dataType:'html'}
	)
}

var getFields = function getFields() {
  var hook = jQuery('#newhook').val();
  var type = jQuery('.newtype:checked').attr('id');
	jQuery.ajax({type:'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_get_fields&hook='+hook+'&type='+type,
    beforeSend:function(){jQuery('#newfields').html('<img src="../wp-content/plugins/hookpress/i/spin.gif" alt="loading..."/>')},
    success:function(html){
      jQuery('#newfields').html(html)},
    dataType:'html'}
	)
};

var newSubmit = function newSubmit() {
  if (!jQuery('#newfields').val()) {
    jQuery('#newindicator').html('<small><?php _e("You must select at least one field to send.","hookpress");?></small>');
    return;
  }
  if (!/^https?:\/\/\w+/.test(jQuery('#newurl').val())) {
    jQuery('#newindicator').html('<small><?php _e("Please enter a valid URL.","hookpress");?></small>');
    return;
  }

  jQuery('#newindicator').html('<img src="../wp-content/plugins/hookpress/i/spin.gif" alt="loading..."/>');

  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_add_fields'
         +'&fields='+jQuery('#newfields').val().join()
         +'&url='+jQuery('#newurl').val()
         +'&type='+jQuery('.newtype:checked').attr('id')
         +'&hook='+jQuery('#newhook').val(),
    beforeSend:function(){
      jQuery('#newsubmit').hide();
      jQuery('#newcancel').hide()
    },
    success:function(html){
      jQuery('#newsubmit').show();
      jQuery('#newcancel').show()
      jQuery('#newindicator').html('');
      if (/^ERROR/.test(html))
        jQuery('#newindicator').html(html);
      else if (!html)
        jQuery('#newindicator').html('<?php _e("There was an unknown error.","hookpress");?>');
      else {
        tb_remove();
        var newhook = jQuery(html);
        newhook.css('background-color','yellow');
        newhook.appendTo(jQuery('#webhooks'));
        setEvents();
        newhook.animate({backgroundColor:'white'},2000,null,
          function(){newhook.css('background-color','transparent')});
      }
    },
    dataType:'html'}
  );
};

var deleteHook = function deleteHook(id) {
  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_delete_hook&id='+id,
    success:function(html){
      if (/^ERROR/.test(html))
        jQuery('#message').html(html);
      else {
        jQuery('#'+id).fadeOut('fast',function(){jQuery('#'+id).remove()});
      }
    },
    dataType:'html'}
  );
}

var setHookEnabled = function setHookEnabled(id,boolean) {
  jQuery.ajax({type: 'POST',
    url:'admin-ajax.php',
    data:'action=hookpress_set_enabled&id='+id+'&enabled='+boolean,
    success:function(html){
      if (/^ERROR/.test(html))
        jQuery('#message').html(html);
      else {
        jQuery('#'+id).fadeOut('fast',function(){
          jQuery('#'+id).replaceWith(html);
          setEvents();
        });
      }
    },
    dataType:'html'}
  );
}

jQuery(document).ready(function(){
  // initial setup
  getHooks();
  // set event handler
  setEvents();
});

var setEvents = function setEvents() {
  jQuery('.newtype').change(getHooks);
  jQuery('#newhook').change(getFields);
  jQuery('#newsubmit').click(newSubmit);
  jQuery('#newcancel').click(tb_remove);
  jQuery('.delete').click(function(e){
    var id = e.currentTarget.id.replace('delete','');
    deleteHook(id);
  });
/*  jQuery('.edit').click(function(e){
    var id = e.currentTarget.id.replace('edit','');
//    setupEditHook(id);
  });*/

  jQuery('.on').click(function(e){
    var id = e.currentTarget.id.replace('on','');
    setHookEnabled(id,'false');
  });
  jQuery('.off').click(function(e){
    var id = e.currentTarget.id.replace('off','');
    setHookEnabled(id,'true');
  });
}

</script>

<div class="wrap">
		<h2>
			<?php _e('HookPress','hookpress');?> <small><?php 
			
			$display_version = $hookpress_version;
			$split = explode('.',$display_version);
			if (strlen($split[1]) != 1) {
				$pos = strpos($display_version,'.')+2;
				$display_version = substr($display_version,0,$pos).'.'.substr($display_version,$pos);
			}
      echo $display_version;
			?></small>
		</h2>

	<?php echo "<div id='hookpress-version' style='display:none;'>$hookpress_version</div>"; ?>
		
	<form method="post">

			<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%2fhookpress%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=1&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8' target='_new'><img src="https://www.paypal.com/<?php echo hookpress_paypal_directory(); ?>i/btn/btn_donate_SM.gif" name="submit" alt="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" title="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal','hookpress');?>" style="float:right" /></a>

	<p><small><?php _e('by <a href="http://mitcho.com/">mitcho (Michael 芳貴 Erlewine)</a>','hookpress');?>. <?php _e('Follow <a href="http://twitter.com/hookpress/">HookPress</a> on Twitter.','hookpress');?></small></p>

	<h3><?php _e("Webhooks","hookpress");?></h3>
	<table id='webhooks'>
	  <thead><tr><th colspan='3'><?php _e("hook","hookpress");?></th><th><?php _e("URL","hookpress");?></th><th><?php _e("fields","hookpress");?></th></tr></thead>
  	<tbody>
  	<?php
  	foreach (get_option('hookpress_webhooks') as $id => $desc) {
  	  if (count($desc))
        echo hookpress_print_webhook($id);
  	}
  	?>
  	</tbody>
	</table>

  <input class="thickbox button" type="button" value="<?php _e("Add webhook",'hookpress');?>" title="<?php _e('Add new webhook','hookpress');?>" alt="#TB_inline?height=330&width=500&inlineId=hookpress-new-webhook"/>

<!--	<h3>General options</h3>
	
	<div>
		<p class="submit">
			<input type="submit" name="update_hookpress" value="<?php _e("Update options",'hookpress')?>" />
			<input type="submit" onclick='return confirm("<?php _e("Do you really want to reset your configuration?",'hookpress');?>");' class="hookpress_warning" name="reset_hookpress" value="<?php _e('Reset options','hookpress')?>" />
		</p>
	</div>-->
		
</form>

<div id='hookpress-new-webhook' style='display:none;'>
<form id='newform'>
<table>
<tr><td><label style='font-weight: bold' for='newhook'><?php _e("WordPress hook type",'hookpress');?>: </label></td><td><input type='radio' id='action' class='newtype' name='newtype' checked='checked'> <?php _e("action","hookpress");?></input> <input type='radio' id='filter' class='newtype' name='newtype'> <?php _e("filter","hookpress");?></input></td></tr>
<tr>
<td><label style='font-weight: bold' for='newhook' id='action_or_filter'></label></td>
<td><select name='newhook' id='newhook'>
    <?php
      $keys = array_keys($hookpress_actions);
      sort($keys);
      foreach ($keys as $hook) {
        echo "<option value='$hook'>$hook</option>";
      }
    ?>
  </select></td></tr>
<tr><td style='vertical-align: top'><label style='font-weight: bold' for='newfields'><?php _e("Fields",'hookpress');?>: </label><br/><small><?php _e("Ctrl-click on Windows or Command-click on Mac to select multiple. The <code>hook</code> field with the relevant hook name is always sent.)");?></small></td><td><select style='vertical-align: top' name='newfields' id='newfields' multiple='multiple' size='8'>
  </select></td></tr>
<tr><td><label style='font-weight: bold' for='newurl'><?php _e("URL",'hookpress');?>: </label></td><td><input name='newurl' id='newurl' size='40' value='http://'></input></td></tr>
</table>

  <center><span id='newindicator'></span><br/>
  <input type='button' class='button' id='newsubmit' value='<?php _e('Add new webhook','hookpress');?>'/>
  <input type='button' class='button' id='newcancel' value='<?php _e('Cancel');?>'/></center>

</form>
</div>