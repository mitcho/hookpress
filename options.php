<script type='text/javascript'>
var getTb;

(function($) {

var thickboxWindow = false;
getTb = function() {
	return jQuery('#TB_window');
}

var getHooks = function getHooks() {
	var type = getTb().find('.newtype:checked').attr('id');
	if (type == 'action') {
		getTb().find('#action_or_filter').text('<?php _e("Action:",'hookpress');?> ');
		getTb().find('#filtermessage').hide();
	}
	if (type == 'filter') {
		getTb().find('#action_or_filter').text('<?php _e("Filter:",'hookpress');?> ');
		getTb().find('#filtermessage').show();
	}
	$.ajax({type:'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_get_hooks&type='+type,
		beforeSend:function(){
			getTb().find('#newhook').html('<div class="webhooks-spinner">&nbsp;</div>');
		},
		success:function(html){
			getTb().find('#newhook').html(html);
			getFields();
		},
		dataType:'html'}
	)
}

var getFields = function getFields() {
	var hook = getTb().find('#newhook').val();
	var type = getTb().find('.newtype:checked').attr('id');
	$.ajax({type:'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_get_fields&hook='+hook+'&type='+type,
		beforeSend:function(){
			getTb().find('#newfields').html('<div class="webhooks-spinner">&nbsp;</div>');
		},
		success:function(html){
			getTb().find('#newfields').html(html)
		},
		dataType:'html'}
	)
};

var getEditHooks = function getEditHooks() {
	var type = getTb().find('.newtype:checked').attr('id');
	if (type == 'action') {
		getTb().find('#action_or_filter').text('<?php _e("Action:",'hookpress');?> ');
		getTb().find('#filtermessage').hide();
	}
	if (type == 'filter') {
		getTb().find('#action_or_filter').text('<?php _e("Filter:",'hookpress');?> ');
		getTb().find('#filtermessage').show();
	}
	$.ajax({type:'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_get_hooks&type='+type,
		beforeSend:function(){
			getTb().find('#edithook').html('<div class="webhooks-spinner">&nbsp;</div>');
		},
		success:function(html){
			getTb().find('#edithook').html(html);
			getEditFields();
		},
		dataType:'html'}
	)
}

var getEditFields = function getEditFields() {
	var hook = getTb().find('#edithook').val();
	var type = getTb().find('.newtype:checked').attr('id');
	$.ajax({type:'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_get_fields&hook='+hook+'&type='+type,
		beforeSend:function(){
			getTb().find('#editfields').html('<div class="webhooks-spinner">&nbsp;</div>');
		},
		success:function(html){
			getTb().find('#editfields').html(html);
		},
		dataType:'html'}
	)
};

var editSubmit = function editSubmit() {
	if (!getTb().find('#editfields').val()) {
		getTb().find('#editindicator').html('<small><?php _e("You must select at least one field to send.","hookpress");?></small>');
		return;
	}
	if (!/^https?:\/\/\w+/.test(getTb().find('#editurl').val())) {
		getTb().find('#editindicator').html('<small><?php _e("Please enter a valid URL.","hookpress");?></small>');
		return;
	}

	getTb().find('#editindicator').html('<div class="webhooks-spinner">&nbsp;</div>');

	id = getTb().find('#edit-hook-id').val();
	
	$.ajax({type: 'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_add_fields'
				 +'&fields='+getTb().find('#editfields').val().join()
				 +'&url='+getTb().find('#editurl').val()
				 +'&type='+getTb().find('.newtype:checked').attr('id')
				 +'&hook='+getTb().find('#edithook').val()
				 +'&enabled='+getTb().find('#enabled').val()
				 +'&id='+id
				 +'&_nonce='+getTb().find('#submit-nonce').val(),
		beforeSend:function(){
			getTb().find('#editsubmit').hide();
			getTb().find('#editcancel').hide()
		},
		success:function(html){
			getTb().find('#editsubmit').show();
			getTb().find('#editcancel').show()
			getTb().find('#editindicator').html('');
			if (/^ERROR/.test(html))
				getTb().find('#editindicator').html(html);
			else if (!html)
				getTb().find('#editindicator').html('<?php _e("There was an unknown error.","hookpress");?>');
			else {
				$('#'+id).replaceWith(html);
				tb_init('a.thickbox, area.thickbox, input.thickbox');
				tb_remove();
			}
		},
		dataType:'html'}
	);
};

var enforceFirst = function enforceFirst() {
	var type = getTb().find('.newtype:checked').attr('id');
	if (type == 'action')
		return;
	getTb().find('option.first').attr('selected',true);
}

var newSubmit = function newSubmit() {
	if (!getTb().find('#newfields').val()) {
		getTb().find('#newindicator').html('<small><?php _e("You must select at least one field to send.","hookpress");?></small>');
		return;
	}
	if (!/^https?:\/\/\w+/.test(getTb().find('#newurl').val())) {
		getTb().find('#newindicator').html('<small><?php _e("Please enter a valid URL.","hookpress");?></small>');
		return;
	}

	getTb().find('#newindicator').html('<div class="webhooks-spinner">&nbsp;</div>');

	$.ajax({type: 'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_add_fields'
				 +'&fields='+getTb().find('#newfields').val().join()
				 +'&url='+getTb().find('#newurl').val()
				 +'&type='+getTb().find('.newtype:checked').attr('id')
				 +'&hook='+getTb().find('#newhook').val()
				 +'&_nonce='+getTb().find('#submit-nonce').val(),
		beforeSend:function(){
			getTb().find('#newsubmit').hide();
			getTb().find('#newcancel').hide()
		},
		success:function(html){
			getTb().find('#newsubmit').show();
			getTb().find('#newcancel').show()
			getTb().find('#newindicator').html('');
			if (/^ERROR/.test(html))
				getTb().find('#newindicator').html(html);
			else if (!html)
				getTb().find('#newindicator').html('<?php _e("There was an unknown error.","hookpress");?>');
			else {
				var newhook = $(html);
				newhook.css('background-color','rgb(255, 251, 204)');
				newhook.appendTo($('#webhooks'));
				tb_init('a.thickbox, area.thickbox, input.thickbox');
				tb_remove();
/*				setEvents(); */
				newhook.animate({backgroundColor:'white'},2000,null,
					function(){newhook.css('background-color','transparent')});
			}
		},
		dataType:'html'}
	);
};

var deleteHook = function deleteHook(id) {
	var nonce = $('#delete-nonce-' + id).val();
	$.ajax({type: 'POST',
		url:'admin-ajax.php',
		beforeSend:function(){$('#' + id + ' span.edit').html('<div class="webhooks-spinner">&nbsp;</div>')},
		data:'action=hookpress_delete_hook&id='+id + '&_nonce=' +nonce,
		success:function(html){
			if (/^ERROR/.test(html))
				$('#message').html(html);
			else {
				$('#'+id).fadeOut('fast',function(){$('#'+id).remove()});
			}
		},
		dataType:'html'}
	);
}

var setHookEnabled = function setHookEnabled(id, nonce, boolean) {
	$.ajax({type: 'POST',
		url:'admin-ajax.php',
	beforeSend:function(){$('#' + id + ' span.edit').html('<div class="webhooks-spinner">&nbsp;</div>')},
		data:'action=hookpress_set_enabled&id='+id+'&_nonce='+nonce+'&enabled='+boolean,
		success:function(html){
			if (/^ERROR/.test(html))
				$('#message').html(html);
			else {
				$('#'+id).fadeOut('fast',function(){
					$('#'+id).replaceWith(html);
					tb_init('a.thickbox, area.thickbox, input.thickbox');
/*					setEvents(); */
				});
			}
		},
		dataType:'html'}
	);
}

var setupEditHook = function setupEditHook(id) {
	$.ajax({type: 'POST',
		url:'admin-ajax.php',
		data:'action=hookpress_edit_hook&id='+id,
		success:function(html){
			$('#TB_ajaxContent').html(html);
			getTb().find('#edithook').change(getEditFields);
			getTb().find('#editfields').change(enforceFirst);
			getTb().find('#editsubmit').click(editSubmit);
			getTb().find('#editcancel').click(tb_remove);
		},
		dataType:'html'}
	);
}

$(document).ready(function(){
	// initial setup
//	getHooks();
	$('#newwebhook').click(function() {
		setTimeout(function() {
			getHooks();
		},0);
	})
	// set event handler
	setEvents();
});

var setEvents = function setEvents() {
	$('#TB_window .newtype').live('change',getHooks);
	$('#TB_window #newhook').live('change',getFields);
	$('#TB_window #newfields').live('change',enforceFirst);
	$('#TB_window #newsubmit').live('click',newSubmit);
	$('#TB_window #newcancel').live('click',tb_remove);

	$('#webhooks .delete').live('click', function(e){
		var id = e.currentTarget.id.replace('delete','');
		deleteHook(id);
	});
	$('#webhooks .edit').live('click', function(e){
		var id = e.currentTarget.id.replace('edit','');
		if(id){setupEditHook(id);}
	});

	$('#webhooks .on').live('click', function(e){
		var id = e.currentTarget.id.replace('on','');
		var nonce = $('#action-nonce-' + id).val();
		if(id && nonce){setHookEnabled(id, nonce, 'false');}
	});
	$('#webhooks .off').live('click', function(e){
		var id = e.currentTarget.id.replace('off','');
		var nonce = $('#action-nonce-' + id).val();
		if(id&&nonce){setHookEnabled(id, nonce, 'true');}
	});
}

})(jQuery);
</script>
<style>
/* styles for 3.2+ */
#webhooks .active {
	background-color: #FCFCFC;
}
#webhooks .inactive {
	background-color: #F4F4F4;
}

/* styles for pre-3.2; only supporing 3.1 now */
.version-3-1 #webhooks .inactive {
	background-color: #eee;
}
.version-3-1 #webhooks .active {
	background-color: transparent;
}

.webhooks-spinner {
	background: url(<?php echo admin_url( 'images/wpspin_light.gif' ); ?>);
	height: 16px;
	width: 16px;
	visibility: visible !important;
}

</style>

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
		
	<form method="post">

			<a href='http://tinyurl.com/donatetomitcho' target='_new'><img src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" title="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal','hookpress');?>" style="float:right" /></a>

	<p><small><?php _e('by <a href="http://mitcho.com/">mitcho (Michael 芳貴 Erlewine)</a>','hookpress');?>.</small></p>

	<h3><?php _e("Webhooks","hookpress");?></h3>

<?php echo hookpress_print_webhooks_table();?>

	<p><input id="newwebhook" class="thickbox button" type="button" value="<?php _e("Add webhook",'hookpress');?>" title="<?php _e('Add new webhook','hookpress');?>" alt="#TB_inline?height=330&width=500&inlineId=hookpress-webhook"/></p>
		
</form>

<div id='hookpress-webhook' style='display:none;'>
<form id='newform'>
<table>
<tr><td><label style='font-weight: bold' for='newhook'><?php _e("WordPress hook type",'hookpress');?>: </label></td><td><input type='radio' id='action' class='newtype' name='newtype' checked='checked'> <?php _e("action","hookpress");?></input> <input type='radio' id='filter' class='newtype' name='newtype'> <?php _e("filter","hookpress");?></input></td></tr>
<tr>
<td><label style='font-weight: bold' for='newhook' id='action_or_filter'></label></td>
<td><select name='newhook' id='newhook'></select></td></tr>
<tr><td style='vertical-align: top'><label style='font-weight: bold' for='newfields'><?php _e("Fields",'hookpress');?>: </label><br/><small><?php _e("Ctrl-click on Windows or Command-click on Mac to select multiple. The <code>hook</code> field with the relevant hook name is always sent.");?></small><br/><span id='filtermessage'><small><?php _e('The first argument of a filter must always be sent and should be returned by the webhook, with modification.','hookpress');?></small></span></td><td><select style='vertical-align: top' name='newfields' id='newfields' multiple='multiple' size='8'>
	</select></td></tr>
<tr><td><label style='font-weight: bold' for='newurl'><?php _e("URL",'hookpress');?>: </label></td><td><input name='newurl' id='newurl' size='40' value='http://'></input></td></tr>
</table>
<?php	echo "<input type='hidden' id='submit-nonce' name='submit-nonce' value='" . wp_create_nonce( 'submit-webhook') . "' />"; ?>
	<center><span id='newindicator'></span><br/>
	<input type='button' class='button' id='newsubmit' value='<?php _e('Add new webhook','hookpress');?>'/>
	<input type='button' class='button' id='newcancel' value='<?php _e('Cancel');?>'/></center>

</form>
</div>