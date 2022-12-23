<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 10:42:30
         compiled from "c01bd5d2abce414af9b33b6905d05b2ac7924e0b" */ ?>
<?php /*%%SmartyHeaderCode:143027927657a2fff6d48a47-71463495%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c01bd5d2abce414af9b33b6905d05b2ac7924e0b' => 
    array (
      0 => 'c01bd5d2abce414af9b33b6905d05b2ac7924e0b',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '143027927657a2fff6d48a47-71463495',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SECOND_CHANCE' => 0,
    'CARDS' => 0,
    'card' => 0,
    'CARD_COLORS' => 0,
    'COLOR_VALUES' => 0,
    'FINAL_ACTION_WRONG' => 0,
    'FINAL_ACTION_RIGHT' => 0,
    'TOTAL_CARD_COUNT' => 0,
    'FINAL_ACTION_END_GAME_WITH_INFORMATION' => 0,
    'image' => 0,
    'SITE_URL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a2fff6e227d6_81765839',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a2fff6e227d6_81765839')) {function content_57a2fff6e227d6_81765839($_smarty_tpl) {?><?php if (!is_callable('smarty_function_cycle')) include '/webspace/16/104471/mind-the-memory.com/modules/core/renderer/smarty/plugins/function.cycle.php';
?><div class="container-fluid<?php if ($_smarty_tpl->tpl_vars['SECOND_CHANCE']->value==1){?> mv-second-chance-main-container<?php }?>" id="card-game-screen">
		<div class="row row-alternate">
				<?php  $_smarty_tpl->tpl_vars['card'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['card']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['CARDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["card_loop"]['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['card']->key => $_smarty_tpl->tpl_vars['card']->value){
$_smarty_tpl->tpl_vars['card']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["card_loop"]['index']++;
?>
						<?php if (!($_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index'] % 8||$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']==0)){?>
								</div>
								<div class="row <?php echo smarty_function_cycle(array('values'=>"row-alternate,row-long-alternate"),$_smarty_tpl);?>
">
						<?php }?>
								<?php if ($_smarty_tpl->tpl_vars['card']->value['card_type']=='image'){?>
										<div class="col-md-offset-2 col-md-1 card" id="card-image-<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['id'];?>
" data-attr-card-id="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['id'];?>
" data-attr-wrong-animation="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['wrong_animations'][1]['filename_with_path'];?>
" data-attr-image="<?php if ($_smarty_tpl->tpl_vars['SECOND_CHANCE']->value==1){?>data/templates/blitz2016/images/bg_wall.png<?php }else{ ?>data/images/cardcovers/<?php echo $_smarty_tpl->tpl_vars['CARD_COLORS']->value[$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']];?>
<?php }?>" data-attr-animation="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['animations'][1]['filename_with_path'];?>
" data-attr-card-type="image" data-attr-card-id-right-answer="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['right_answer'];?>
">
												<div class="card_border_container">
														<img src="<?php if ($_smarty_tpl->tpl_vars['SECOND_CHANCE']->value==1){?>data/templates/blitz2016/images/bg_wall.png<?php }else{ ?>data/images/cardcovers/<?php echo $_smarty_tpl->tpl_vars['CARD_COLORS']->value[$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']];?>
<?php }?>" class="img-responsive" data-attr-test="<?php echo $_smarty_tpl->tpl_vars['CARD_COLORS']->value[$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']];?>
" />
														<div class="image-card-highlite-container">
																<div class="image-card-highlite-inner-container"></div>
														</div>
												</div>
										</div>
								<?php }else{ ?>
										<div class="col-md-offset-2 col-md-1 card" id="card-text-<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['id'];?>
" data-attr-card-id="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['id'];?>
" data-attr-card-type="text" data-attr-card-id-right-answer="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['right_answer'];?>
" data-attr-image="<?php if ($_smarty_tpl->tpl_vars['SECOND_CHANCE']->value==1){?>data/templates/blitz2016/images/bg_wall.png<?php }else{ ?>data/images/cardcovers/<?php echo $_smarty_tpl->tpl_vars['CARD_COLORS']->value[$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']];?>
<?php }?>" data-attr-image-show="<?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['images'][3]['filename_with_path'];?>
">
												<div class="card_border_container">
														<div class="card_text" <?php if ($_smarty_tpl->tpl_vars['SECOND_CHANCE']->value!=1){?>style="border-color: <?php echo $_smarty_tpl->tpl_vars['COLOR_VALUES']->value[$_smarty_tpl->tpl_vars['CARD_COLORS']->value[$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']]];?>
<?php }else{ ?>style="border-color: #292929;"<?php }?>">
																<img src="<?php if ($_smarty_tpl->tpl_vars['SECOND_CHANCE']->value==1){?>data/templates/blitz2016/images/bg_wall.png<?php }else{ ?>data/images/cardcovers/<?php echo $_smarty_tpl->tpl_vars['CARD_COLORS']->value[$_smarty_tpl->getVariable('smarty')->value['foreach']['card_loop']['index']];?>
<?php }?>" class="img-responsive" />
																<div class="card_text_info">
																		<div class="the-text">
																				<p><?php echo $_smarty_tpl->tpl_vars['card']->value['card_data']['titles'][0]['title1'];?>
</p>
																		</div>
																</div>
														</div>
														
												</div>
										</div>
								<?php }?>
				<?php } ?>
		</div>
</div>

<input type="hidden" id="final_action_wrong" value="<?php echo $_smarty_tpl->tpl_vars['FINAL_ACTION_WRONG']->value;?>
" />
<input type="hidden" id="final_action_right" value="<?php echo $_smarty_tpl->tpl_vars['FINAL_ACTION_RIGHT']->value;?>
" />
<input type="hidden" id="second_chance" value="<?php echo $_smarty_tpl->tpl_vars['SECOND_CHANCE']->value;?>
" />
<input type="hidden" id="total_card_count" value="<?php echo $_smarty_tpl->tpl_vars['TOTAL_CARD_COUNT']->value;?>
" />
<input type="hidden" id="final_action_end_game_with_information" value="<?php echo $_smarty_tpl->tpl_vars['FINAL_ACTION_END_GAME_WITH_INFORMATION']->value;?>
" />

<div id="load-waiter"><p>loading<span id="animate_dottes"></span></p></div>

<style>
		#animate_dottes { position: fixed; color: #FFF; }
</style>


<script>
		$(function() {
				var dottes = 0;
				var mv_animated_dottes_timeout;
				
				function animate_dottes() {
						var dottes_text = '';
						
						dottes++;
						
						for(var i = 0; i < dottes; i++) {
								dottes_text += '.';
						}
						
						if(dottes == 3) {
								dottes = -1;
						}
					
						$('#animate_dottes').html(dottes_text);
						mv_animated_dottes_timeout = setTimeout(function() { animate_dottes(); }, 300);
				}
			
				mv_animated_dottes_timeout = setTimeout(function() { animate_dottes(); }, 300);
		});
		
		var mv_preload_images = [<?php  $_smarty_tpl->tpl_vars['card'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['card']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['CARDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['image_preload_loop']['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars['card']->key => $_smarty_tpl->tpl_vars['card']->value){
$_smarty_tpl->tpl_vars['card']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['image_preload_loop']['iteration']++;
?><?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['image']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['card']->value['card_data']['images']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['image_loop']['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
$_smarty_tpl->tpl_vars['image']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['image_loop']['iteration']++;
?><?php if ($_smarty_tpl->tpl_vars['image']->value!=''){?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['image_preload_loop']['iteration']!=1||$_smarty_tpl->getVariable('smarty')->value['foreach']['image_loop']['iteration']!=1){?>, <?php }?>'<?php echo $_smarty_tpl->tpl_vars['image']->value['filename_with_path'];?>
'<?php }?><?php } ?><?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['image']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['card']->value['card_data']['animations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
$_smarty_tpl->tpl_vars['image']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['image']->value!=''){?>, '<?php echo $_smarty_tpl->tpl_vars['image']->value['filename_with_path'];?>
'<?php }?><?php } ?><?php  $_smarty_tpl->tpl_vars['image'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['image']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['card']->value['card_data']['wrong_animations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['image']->key => $_smarty_tpl->tpl_vars['image']->value){
$_smarty_tpl->tpl_vars['image']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['image']->value!=''){?>, '<?php echo $_smarty_tpl->tpl_vars['image']->value['filename_with_path'];?>
'<?php }?><?php } ?><?php } ?>];
</script>

<style>
		html, body { height: 100%; }
</style>


<div id="global-restart-button"><a href="<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
">restart</a></div>

<style>
		#global-restart-button { position: absolute; right: 5%; bottom: 8px; }
		#global-restart-button a { color: #FFF; }
</style>
<?php }} ?>