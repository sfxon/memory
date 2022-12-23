<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 10:42:06
         compiled from "293e582f613d314c200ead261e1de19a2aebb303" */ ?>
<?php /*%%SmartyHeaderCode:198053475957a2ffde862344-67963946%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '293e582f613d314c200ead261e1de19a2aebb303' => 
    array (
      0 => '293e582f613d314c200ead261e1de19a2aebb303',
      1 => 0,
      2 => 'string',
    ),
  ),
  'nocache_hash' => '198053475957a2ffde862344-67963946',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'TEMPLATE_URL' => 0,
    'TEMPLATE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a2ffde8736c1_06149564',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a2ffde8736c1_06149564')) {function content_57a2ffde8736c1_06149564($_smarty_tpl) {?><div id="index-page" class="fix-button-to-bottom">
		<div id="index-teaser-image">
				<video controls autoplay loop>
						<source src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/videos/video3.mp4" type="video/mp4">
						Your browser does not support the video tag.
				</video> 
				<div id="index-teaser-image-hide-controls" style="position: absolute; bottom: 0; height: 50px; width: 100%; top: auto; background-color: #000;"><?php echo @MEMORY_TEXT_19;?>
</div>
				<!-- <img src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/images/startscreen.gif" class="img-responsive"> -->
				
		</div>
		<br />
		
		<a href="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
start.html" id="mv-button-bottom"><button class="mv-btn"><?php echo @MEMORY_TEXT_2;?>
</button></a>
</div>


		<style>
				html, body { min-height: 100%; height: 100%; }
				#index-page { padding-top: 13%; }
				.fix-button-to-bottom { position: relative; height: 100%; }
				#mv-button-bottom { bottom: 180px; position: absolute; margin-left: -120px; }
				/*#mv-button-bottom { display: none; }*/
				#index-teaser-image-hide-controls { text-align: left; padding-left: 20px; padding-top: 20px; }
		</style>
		
		<script>
				function show_button() {
						$('#index-teaser-image-hide-controls').css('margin-left', (parseInt($('video').position().left)) + 'px');
						$('#index-teaser-image-hide-controls').show();
				}
				
				$(function() {
						setTimeout( function() { show_button(); }, 600);
				});
		</script>
<?php }} ?>