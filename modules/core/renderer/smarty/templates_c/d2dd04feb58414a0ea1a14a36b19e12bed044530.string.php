<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 12:14:55
         compiled from "d2dd04feb58414a0ea1a14a36b19e12bed044530" */ ?>
<?php /*%%SmartyHeaderCode:167552882157a3159f9b1030-14997495%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd2dd04feb58414a0ea1a14a36b19e12bed044530' => 
    array (
      0 => 'd2dd04feb58414a0ea1a14a36b19e12bed044530',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '167552882157a3159f9b1030-14997495',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SITE_URL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a3159f9e5d92_36334392',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a3159f9e5d92_36334392')) {function content_57a3159f9e5d92_36334392($_smarty_tpl) {?><div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"></p>
						
								<script>
										$(function(){
												$(".mv-type-text").typed({
														strings: ["<?php echo @MEMORY_TEXT_5;?>
"],
														typeSpeed: 0,
														callback: function() {
																$('#mv-button-bottom').show();
																/*setTimeout(function() { restart_game(); }, 20000);*/
														}
												});
												
												
												function restart_game() {
														var url = "<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
game.html?action=restart";
														window.location = url;
												}
										});
								</script>
						
						
						
						<style>
								#try_again_button { display: none; }
						</style>
						

		<a href="<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
game.html?action=restart" id="mv-button-bottom"><button class="mv-btn"><?php echo @MEMORY_TEXT_6;?>
</button></a>
</div>


		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { display: none; }
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