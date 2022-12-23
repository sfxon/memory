<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 10:42:10
         compiled from "89c960c521156cebad2b248087afff2b03c31180" */ ?>
<?php /*%%SmartyHeaderCode:141291451057a2ffe21fa1d3-77566565%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '89c960c521156cebad2b248087afff2b03c31180' => 
    array (
      0 => '89c960c521156cebad2b248087afff2b03c31180',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '141291451057a2ffe21fa1d3-77566565',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'TEMPLATE_URL' => 0,
    'SITE_URL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a2ffe223b0e4_15137475',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a2ffe223b0e4_15137475')) {function content_57a2ffe223b0e4_15137475($_smarty_tpl) {?><div class="fix-button-to-bottom" id="start-intro-page">
		<p class="text-left mv-type-text"></p>
														
		
				<script>
						function mv_show_button() {
								$('#mv-button-bottom').show();
						}
						
						$(function(){
								$(".mv-type-text").typed({
										strings: ["<?php echo @MEMORY_TEXT_9;?>
<?php if (isset($_SESSION['name'])&&!empty($_SESSION['name'])){?>, <?php echo $_SESSION['name'];?>
<?php }?><?php echo @MEMORY_TEXT_10;?>
<br /><?php echo @MEMORY_TEXT_11;?>
"],
										typeSpeed: 0,
										callback: function() {
												mv_show_button();
										}
								});
						});
				</script>
		
		
		
		
		<a href="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
game.html" id="mv-button-bottom"><button type="submit" class="mv-btn"><?php echo @MEMORY_TEXT_12;?>
</button></a>
</div>


		<style>
				html, body { min-height: 100%; height: 100%; }
				#start-intro-page { padding-top: 120px; margin-top: 0; position: relative; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { display: none; text-align: center; }
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