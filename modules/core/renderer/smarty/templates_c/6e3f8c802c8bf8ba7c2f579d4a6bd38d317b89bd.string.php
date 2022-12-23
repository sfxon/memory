<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 12:03:57
         compiled from "6e3f8c802c8bf8ba7c2f579d4a6bd38d317b89bd" */ ?>
<?php /*%%SmartyHeaderCode:146703655957a3130da750a8-80676974%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6e3f8c802c8bf8ba7c2f579d4a6bd38d317b89bd' => 
    array (
      0 => '6e3f8c802c8bf8ba7c2f579d4a6bd38d317b89bd',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '146703655957a3130da750a8-80676974',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SITE_URL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a3130db41406_59264079',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a3130db41406_59264079')) {function content_57a3130db41406_59264079($_smarty_tpl) {?><div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"><br /></p>																
		
				<script>
						$(function(){
								$(".mv-type-text").typed({
										strings: ["<?php echo @MEMORY_TEXT_17;?>
"],
										typeSpeed: 0,
										callback: function() {
												$('#try_again_button').show();
												setTimeout(function() { restart_game(); }, 3000);
										}
								});
								
								
								function restart_game() {
										var url = "<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
";
										window.location = url;
								}
						});
				</script>
		
		
		
		<style>
				#try_again_button { display: none; }
		</style>
		

		<div id="try_again_button">
				<a href="<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
"><button class="mv-btn"><?php echo @MEMORY_TEXT_18;?>
</button></a>
		</div>
</div>


		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { bottom: 180px; position: absolute; margin-left: -80px; display: none; }
				#index-teaser-image-hide-controls { text-align: left; padding-left: 20px; padding-top: 20px; }
				#start-intro-page { padding-top: 0; }
				#start-intro-page p { padding: 0 5%; font-size: 45px; padding-top: 0; }
		</style>


<div id="global-restart-button"><a href="<?php echo $_smarty_tpl->tpl_vars['SITE_URL']->value;?>
">restart</a></div>

<style>
		#global-restart-button { position: absolute; right: 5%; bottom: 8px; }
		#global-restart-button a { color: #FFF; }
</style>
<?php }} ?>