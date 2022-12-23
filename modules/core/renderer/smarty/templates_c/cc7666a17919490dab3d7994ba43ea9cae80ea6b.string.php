<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 12:18:19
         compiled from "cc7666a17919490dab3d7994ba43ea9cae80ea6b" */ ?>
<?php /*%%SmartyHeaderCode:48010083457a3166bc85e19-45640397%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cc7666a17919490dab3d7994ba43ea9cae80ea6b' => 
    array (
      0 => 'cc7666a17919490dab3d7994ba43ea9cae80ea6b',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '48010083457a3166bc85e19-45640397',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SITE_URL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a3166bcdd165_83223718',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a3166bcdd165_83223718')) {function content_57a3166bcdd165_83223718($_smarty_tpl) {?><div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"></p>
		
				<script>
						$(function(){
								$(".mv-type-text").typed({
										strings: ["<?php echo @MEMORY_TEXT_15;?>
"],
										typeSpeed: 0,
										callback: function() {
												$('#try_again_button').show();
										}
								});
						});
				</script>
		
		
		
		<style>
				#try_again_button { display: none; }
		</style>
		
	
		<div id="try_again_button">
				<a href="<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
"><button class="mv-btn"><?php echo @MEMORY_TEXT_16;?>
</button></a>
		</div>

</div>


		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#try_again_button { bottom: 180px; position: absolute; width: 100%; text-align: center; }
				#index-teaser-image-hide-controls { text-align: left; padding-left: 20px; padding-top: 20px; }
				#start-intro-page { padding-top: 0; }
				#start-intro-page p { padding: 0 5%; font-size: 45px; padding-top: 0; width: 100%; }
		</style>


<div id="global-restart-button"><a href="<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
">restart</a></div>

<style>
		#global-restart-button { position: absolute; right: 5%; bottom: 8px; }
		#global-restart-button a { color: #FFF; }
</style>
<?php }} ?>