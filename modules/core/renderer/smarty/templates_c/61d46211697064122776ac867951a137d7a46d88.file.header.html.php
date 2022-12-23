<?php /* Smarty version Smarty-3.1.11, created on 2016-08-04 10:42:06
         compiled from "data/templates/blitz2016/site/header.html" */ ?>
<?php /*%%SmartyHeaderCode:212971530357a2ffde5b9a88-94192799%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '61d46211697064122776ac867951a137d7a46d88' => 
    array (
      0 => 'data/templates/blitz2016/site/header.html',
      1 => 1470299144,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '212971530357a2ffde5b9a88-94192799',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'META_DESCRIPTION' => 0,
    'META_AUTHOR' => 0,
    'TEMPLATE_URL' => 0,
    'TEMPLATE' => 0,
    'META_TITLE' => 0,
    'FONT_SIZE_TEXT' => 0,
    'FONT_SIZE_BUTTON' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_57a2ffde656a83_66006701',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_57a2ffde656a83_66006701')) {function content_57a2ffde656a83_66006701($_smarty_tpl) {?><!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <?php if (isset($_smarty_tpl->tpl_vars['META_DESCRIPTION']->value)){?><meta name="description" content="<?php echo $_smarty_tpl->tpl_vars['META_DESCRIPTION']->value;?>
"><?php }?>
    <?php if (isset($_smarty_tpl->tpl_vars['META_AUTHOR']->value)){?><meta name="author" content="<?php echo $_smarty_tpl->tpl_vars['META_AUTHOR']->value;?>
"><?php }?>
    <link rel="icon" href="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/images/favicon.ico">
    <title><?php echo $_smarty_tpl->tpl_vars['META_TITLE']->value;?>
</title>
    <link href="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/css/mindfav.css" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
		
		<script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/jquery.min.js"></script>
		<script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/bootstrap.min.js"></script>
		
		<script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/ie10-viewport-bug-workaround.js"></script>
		<!-- <script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/mv.js"></script> -->
		<script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/typed.js"></script>
    
		
    <!--[if lt IE 9]>
      <script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/html5shiv.min.js"></script>
      <script src="<?php echo $_smarty_tpl->tpl_vars['TEMPLATE_URL']->value;?>
data/templates/<?php echo $_smarty_tpl->tpl_vars['TEMPLATE']->value;?>
/js/respond.min.js"></script>
    <![endif]-->
		<style>
				
				body { font-size: <?php echo $_smarty_tpl->tpl_vars['FONT_SIZE_TEXT']->value;?>
; }
				button, input[type=submit] { font-size: <?php echo $_smarty_tpl->tpl_vars['FONT_SIZE_BUTTON']->value;?>
; }
				
		</style>
		
		<script>
				var mv_animated_dottes_timeout = '';
		</script>
		<?php }} ?>