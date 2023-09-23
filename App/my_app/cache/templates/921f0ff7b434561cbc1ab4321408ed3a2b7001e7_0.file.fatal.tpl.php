<?php
/* Smarty version 3.1.46, created on 2023-02-04 12:21:41
  from '/home/polo/Projekty/simfra/web/App/my_app/templates/Exception/fatal.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.46',
  'unifunc' => 'content_63de3fc54461b7_56547525',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '921f0ff7b434561cbc1ab4321408ed3a2b7001e7' => 
    array (
      0 => '/home/polo/Projekty/simfra/web/App/my_app/templates/Exception/fatal.tpl',
      1 => 1536998999,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_63de3fc54461b7_56547525 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<head>
<title>MVC - Exception <?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
<style type="text/css">
<!--
#content_div{
    display: block;font-family: monospace;padding: 9.5px;margin: 0 0 50px;font-size: 13px;line-height: 1.42857143;color: #333;word-break: break-all;word-wrap: break-word;background-color: #f5f5f5;border: 1px solid #ccc;border-radius: 4px;
    min-height: 200px;max-height: 400px;overflow: auto;
}

body{
    background-color: #f6f6f6; width: 100%;height:100%; margin: 0px; padding: 0px;
}

#header {
    width: 100%; min-height: 150px;background-color: #22395c;color: whitesmoke;
}
-->
</style>
</head>
<body>
<div id="header">
    <div style="padding: 20px;line-height: 30px;"><h1>FATAL ERROR</h1>
    Exception: <b><?php echo $_smarty_tpl->tpl_vars['name']->value;?>
</b> <?php if ((isset($_smarty_tpl->tpl_vars['debug_info']->value['class']))) {?>in Class: <b><?php echo $_smarty_tpl->tpl_vars['debug_info']->value['class'];?>
</b><?php }?> <?php if ((isset($_smarty_tpl->tpl_vars['debug_info']->value[1]['function']))) {?> Function: <b><?php echo $_smarty_tpl->tpl_vars['debug_info']->value[1]['function'];?>
</b><?php }?> <?php if ((isset($_smarty_tpl->tpl_vars['debug_info']->value['line']))) {?> Line: <b><?php echo $_smarty_tpl->tpl_vars['debug_info']->value['line'];?>
</b><?php }?><br />Message: <b><?php echo $_smarty_tpl->tpl_vars['message']->value;?>
</b>
    </div>
</div>
<div style="margin: 50px;">
    <div id="content_div">
        <h2>Debug info</h2>
        <pre>
        <?php echo print_r($_smarty_tpl->tpl_vars['debug_info']->value,true);?>

        </pre>
    </div>
    <div id="content_div">
        <h2>Runtime content</h2>
        <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

    </div>
</div>  
</body><?php }
}
