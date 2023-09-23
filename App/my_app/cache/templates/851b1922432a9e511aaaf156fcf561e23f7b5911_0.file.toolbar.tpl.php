<?php
/* Smarty version 3.1.46, created on 2023-02-04 12:25:29
  from '/home/polo/Projekty/simfra/web/App/my_app/templates/Debug/toolbar.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.46',
  'unifunc' => 'content_63de40a92fb206_99662883',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '851b1922432a9e511aaaf156fcf561e23f7b5911' => 
    array (
      0 => '/home/polo/Projekty/simfra/web/App/my_app/templates/Debug/toolbar.tpl',
      1 => 1675509926,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_63de40a92fb206_99662883 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->smarty->ext->_tplFunction->registerTplFunctions($_smarty_tpl, array (
  'list' => 
  array (
    'compiled_filepath' => '/home/polo/Projekty/simfra/web/App/my_app/cache/templates/851b1922432a9e511aaaf156fcf561e23f7b5911_0.file.toolbar.tpl.php',
    'uid' => '851b1922432a9e511aaaf156fcf561e23f7b5911',
    'call_name' => 'smarty_template_function_list_180549730763de40a92da6e0_39239387',
  ),
));
?>
<div id="toolbar">
	<div class="toolbar-navbar toolbar-navbar-bottom<?php if ($_smarty_tpl->tpl_vars['minimalized_toolbar']->value == true) {?> toolbar-minimalizes_toolbar<?php } else { ?> full_toolbar<?php }?>">
		<ul>
			<li title="HTTP Status" class="toolbar-navbar-status <?php if (count($_smarty_tpl->tpl_vars['dev']->value['errors']['warning']) > 0) {?> devtoolbar_status_warning<?php } elseif (count($_smarty_tpl->tpl_vars['dev']->value['errors']['notice']) > 0) {?>devtoolbar_status_notice font_grey<?php } else { ?>devtoolbar_status_ok<?php }?>">
				<?php echo $_smarty_tpl->tpl_vars['dev']->value['http'];?>

			</li>
			<?php if ((isset($_smarty_tpl->tpl_vars['debug_buffer']->value))) {?>
				<li>
					<i class="fas fa-exclamation-triangle"></i>
					<div class="toolbar-panel" style="min-width: 600px">
						<div class="toolbar-panel-head">
							<h6 class="toolbar-panel-title" ><i class="fas fa-exclamation-triangle"></i>Buffered output</h6>
						</div>
						<div class="toolbar-panel-body">
							<div style="padding: 15px;max-height: 400px;  line-height: 18px; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif !important; font-size: 13px; font-weight: normal; background-color: #fff; color: #000;">
								<?php echo $_smarty_tpl->tpl_vars['debug_buffer']->value;?>

							</div>
						</div>
					</div>

				</li>
			<?php }?>
			<li class="toolbar-navbar-route">
				<i class="fa fa-tasks"></i>
				<?php if ($_smarty_tpl->tpl_vars['ismobile']->value != 1) {?><span> <?php echo $_smarty_tpl->tpl_vars['dev']->value['lang'];?>
 | <?php echo $_smarty_tpl->tpl_vars['dev']->value['page']['controller'];?>
::<?php echo $_smarty_tpl->tpl_vars['dev']->value['page']['method'];?>
 </span><?php }?>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title"><i class="fa fa-tasks"></i>Application info</h6>
					</div>
					<div class="toolbar-panel-body">
						<ul>
							<li><div class="toolbar-first">Application name:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['dev']->value['page']['app'];?>
</div></li>
							<li><div class="toolbar-first">Controller:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['dev']->value['page']['controller'];?>
</div></li>
							<li><div class="toolbar-first">Method:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['dev']->value['page']['method'];?>
</div></li>
							<li><div class="toolbar-first">Class:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['dev']->value['class_path'];?>
</div></li>
							<li><div class="toolbar-first">Route:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['dev']->value['page']['route'];?>
</div></li>
							<li><div class="toolbar-first">Session:</div><div class="toolbar-second"><?php if ($_smarty_tpl->tpl_vars['dev']->value['session'] != '') {
echo $_smarty_tpl->tpl_vars['dev']->value['session'];
} else { ?>none<?php }?></div></li>
							<li><div class="toolbar-first">PHP Version:</div><div class="toolbar-second"><?php echo phpversion();?>
</div></li>
							<li><div class="toolbar-first">Server:</div><div class="toolbar-second"><?php echo php_uname("s");?>
 - <?php echo $_SERVER['SERVER_SOFTWARE'];?>
</div></li>
						</ul>
					</div>
				</div>
			</li>
			<?php if ((isset($_smarty_tpl->tpl_vars['logged_user']->value))) {?>
				<li class="toolbar-navbar-user">
					<i class="fas fa-user"></i>
					<?php if ($_smarty_tpl->tpl_vars['ismobile']->value != 1) {?><span> <?php echo ucfirst($_smarty_tpl->tpl_vars['logged_user']->value['username']);?>
</span><?php }?>
					<div class="toolbar-panel">
						<div class="toolbar-panel-head">
							<h6 class="toolbar-panel-title" ><i class="fas fa-user"></i>User information</h6>
						</div>
						<div class="toolbar-panel-body">
							<ul>
								<li><div class="toolbar-first">User:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['logged_user']->value['firstname'];?>
 <?php echo $_smarty_tpl->tpl_vars['logged_user']->value['lastname'];?>
</div></li>
								<li><div class="toolbar-first">Email:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['logged_user']->value['email'];?>
</div></li>
								<li><div class="toolbar-first">Phone:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['logged_user']->value['phone'];?>
</div></li>
								<li><div class="toolbar-first">Language:</div><div class="toolbar-second"><?php echo $_smarty_tpl->tpl_vars['logged_user']->value['language'];?>
</div></li>
								<li><div class="toolbar-first">Permissions:</div><div class="toolbar-second" style="max-width: 400px;"><?php echo implode(", ",$_smarty_tpl->tpl_vars['logged_user']->value['permissions']);?>
</div></li>
							</ul>
						</div>
					</div>
				</li>
			<?php }?>
			<li>
				<i class="far fa-clock"></i><span title="Execution time"> <?php echo $_smarty_tpl->tpl_vars['dev']->value['time'];?>
 ms</span>
			</li>
			<li>
				<i class="fas fa-save"></i><span title="Memory usage"><?php echo $_smarty_tpl->tpl_vars['dev']->value['memory'];?>
 kb</span>
			</li>
			<li>
				<i class="fas fa-retweet"></i><span id="ajax_request" class="toolbar-label toolbar-label-info" data-count="0">0</span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title"><i class="fas fa-retweet"></i>Ajax requests</h6>
					</div>
					<div class="toolbar-panel-body">
						<ul id="ajax_request_list">
						</ul>
					</div>
				</div>
			</li>
			<li>
				<i class="fas fa-database"></i>
				<span title="Database queries">
				        <?php if ((isset($_smarty_tpl->tpl_vars['dev']->value['database']['queries']))) {?>
						<?php $_smarty_tpl->_assignInScope('a', (count($_smarty_tpl->tpl_vars['dev']->value['database']['queries'])));?>Queries: <?php echo $_smarty_tpl->tpl_vars['a']->value;
if ($_smarty_tpl->tpl_vars['ismobile']->value != 1) {?> (<?php echo round($_smarty_tpl->tpl_vars['dev']->value['database']['time'],"4");?>
ms)<?php }?>
					<?php } else { ?>
						0 w (0.000 ms)
					<?php }?>
			</span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title" ><i class="fas fa-database"></i>List database queries</h6>
					</div>
					<div class="toolbar-panel-body  toolbar-max300">
						<ul><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['dev']->value['database']['queries'], 'value', false, 'counter');
$_smarty_tpl->tpl_vars['value']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['counter']->value => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->do_else = false;
?>
								<li><span class="toolbar-label toolbar-label-info toolbar-small-margin"><?php echo $_smarty_tpl->tpl_vars['counter']->value+1;?>
</span><span class="toolbar-list-span">
									<?php if ($_smarty_tpl->tpl_vars['value']->value['result'] == true) {?><span class="toolbar-result_green">[ OK ]</span><?php } else { ?><span class="toolbar-result_red">[ERROR]</span><?php }?>
										<span class="toolbar-db_query" title="File: <?php echo $_smarty_tpl->tpl_vars['value']->value['query_file'];?>
 - Function: <?php echo $_smarty_tpl->tpl_vars['value']->value['query_function'];
if ($_smarty_tpl->tpl_vars['value']->value['result'] == false) {?>&#013;Error: <?php echo $_smarty_tpl->tpl_vars['value']->value['error_message'];
} else { ?>&#013;Number of rows in result/rows affected: <?php echo $_smarty_tpl->tpl_vars['value']->value['rows_count'];
}?>"><?php echo $_smarty_tpl->tpl_vars['value']->value['query'];?>
 - <span class="toolbar-result_green">Time: <?php echo round($_smarty_tpl->tpl_vars['value']->value['time'],"4");?>
 ms</span></span>
								</span></li>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
						</ul>
					</div>
				</div>
			</li>
			<li>
				<?php $_smarty_tpl->_assignInScope('a', (count($_smarty_tpl->tpl_vars['dev']->value['files'])));?>
				<i class="fas fa-file-alt"></i><span title="Files" class="toolbar-label toolbar-label-info"><?php echo $_smarty_tpl->tpl_vars['a']->value;?>
</span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title" ><i class="fas fa-file-alt"></i>List of used files</h6>
					</div>
					<div class="toolbar-panel-body  toolbar-max300">
						<ul><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['dev']->value['files'], 'value', false, 'counter');
$_smarty_tpl->tpl_vars['value']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['counter']->value => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->do_else = false;
?>
								<li><span class="toolbar-label toolbar-label-info toolbar-small-margin"><?php echo $_smarty_tpl->tpl_vars['counter']->value+1;?>
</span><span class="toolbar-list-span"><?php echo $_smarty_tpl->tpl_vars['value']->value;?>
</span></li>
							<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></ul>
					</div>
				</div>
			</li>
			<?php $_smarty_tpl->_assignInScope('notice', count($_smarty_tpl->tpl_vars['dev']->value['errors']['notice']));?>
			<?php $_smarty_tpl->_assignInScope('warning', count($_smarty_tpl->tpl_vars['dev']->value['errors']['warning']));?>
			<li title="Errors">
				<i class="fas fa-cogs"></i><span class="toolbar-label toolbar-label-success"> <?php if ((isset($_smarty_tpl->tpl_vars['dev']->value['errors']))) {?> <?php echo $_smarty_tpl->tpl_vars['notice']->value+$_smarty_tpl->tpl_vars['warning']->value;
} else { ?> 0 <?php }?></span>
			</li>
			<?php if ($_smarty_tpl->tpl_vars['notice']->value > 0 || $_smarty_tpl->tpl_vars['warning']->value > 0) {?>
				<li><span class="toolbar-navbar-error"><?php if ($_smarty_tpl->tpl_vars['ismobile']->value != 1) {?>NOTICE: </span><?php }?><span class="toolbar-label toolbar-label-warning"><?php echo $_smarty_tpl->tpl_vars['notice']->value;?>
</span>
					<?php if (count($_smarty_tpl->tpl_vars['dev']->value['errors']['notice']) > 0) {?>
						<div class="toolbar-panel">
							<div class="toolbar-panel-head">
								<h6 class="toolbar-panel-title fas fa-cogs" ><i class="fas fa-cogs"></i>Notices</h6>
							</div>
							<div class="toolbar-panel-body toolbar-max300">
								<ul>
									<?php if ($_smarty_tpl->tpl_vars['dev']->value['errors']) {?>
									<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['dev']->value['errors']['notice'], 'value', false, 'key');
$_smarty_tpl->tpl_vars['value']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->do_else = false;
?>
										<li title="File: <?php echo $_smarty_tpl->tpl_vars['value']->value['file'];?>
 - Line: <?php echo $_smarty_tpl->tpl_vars['value']->value['line'];?>
"><span class="toolbar-label toolbar-label-warning toolbar-small-margin"><?php echo $_smarty_tpl->tpl_vars['key']->value+1;?>
</span><span class="toolbar-list-span"><?php echo $_smarty_tpl->tpl_vars['value']->value['error'];?>
</span></li>
									<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
									<?php }?>
								</ul>
							</div>
						</div>
					<?php }?>
				</li>
				<li><span class="toolbar-navbar-error"><?php if ($_smarty_tpl->tpl_vars['ismobile']->value != 1) {?>WARNING: </span><?php }?><span class="toolbar-label toolbar-label-danger"><?php echo $_smarty_tpl->tpl_vars['warning']->value;?>
</span>
					<?php if (count($_smarty_tpl->tpl_vars['dev']->value['errors']['warning']) > 0) {?>
						<div class="toolbar-panel">
							<div class="toolbar-panel-head">
								<h6 class="toolbar-panel-title" ><i class="fas fa-cogs"></i>Warnings</h6>
							</div>
							<div class="toolbar-panel-body toolbar-max300">
								<ul>
									<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['dev']->value['errors']['warning'], 'value', false, 'key');
$_smarty_tpl->tpl_vars['value']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->do_else = false;
?>
										<li title="File: <?php echo $_smarty_tpl->tpl_vars['value']->value['file'];?>
 - Line: <?php echo $_smarty_tpl->tpl_vars['value']->value['line'];?>
"><span class="toolbar-label toolbar-label-danger toolbar-small-margin"><?php echo $_smarty_tpl->tpl_vars['key']->value+1;?>
</span><span class="toolbar-list-span"><?php echo $_smarty_tpl->tpl_vars['value']->value['error'];?>
</span></li>
									<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
								</ul>
							</div>
						</div>
					<?php }?>
				</li>
			<?php }?>
			<li>
				<i class="fas fa-desktop"></i><span><?php if ($_smarty_tpl->tpl_vars['ismobile']->value != 1) {?>Template Info<?php }?></span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title" ><i class="fas fa-desktop"></i>Variables assigned in template system</h6>
					</div>
					<div class="toolbar-panel-body toolbar-max300">
						<ul id="template_vars">
							<?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'list', array('data'=>$_smarty_tpl->tpl_vars['dev_templates']->value), true);?>

						</ul>
					</div>
				</div>
			</li>
		</ul>
	</div>
</div><?php }
/* smarty_template_function_list_180549730763de40a92da6e0_39239387 */
if (!function_exists('smarty_template_function_list_180549730763de40a92da6e0_39239387')) {
function smarty_template_function_list_180549730763de40a92da6e0_39239387(Smarty_Internal_Template $_smarty_tpl,$params) {
$params = array_merge(array('level'=>0), $params);
foreach ($params as $key => $value) {
$_smarty_tpl->tpl_vars[$key] = new Smarty_Variable($value, $_smarty_tpl->isRenderingCache);
}
$_smarty_tpl->_assignInScope('number', 1);
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['data']->value, 'entry');
$_smarty_tpl->tpl_vars['entry']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['entry']->key => $_smarty_tpl->tpl_vars['entry']->value) {
$_smarty_tpl->tpl_vars['entry']->do_else = false;
$__foreach_entry_0_saved = $_smarty_tpl->tpl_vars['entry'];
?>
	<?php if (is_array($_smarty_tpl->tpl_vars['entry']->value)) {?>
		<li class="toolbar-list-folding"><div><span class="toolbar-label-list toolbar-label-info"><?php echo $_smarty_tpl->tpl_vars['number']->value;?>
</span> <b><?php echo $_smarty_tpl->tpl_vars['entry']->key;?>
</b><span id="plus" class="toolbar-label-list toolbar-label-success dev_toolbar_plus">+</span></div>
			<ul style="display: none;"><?php $_smarty_tpl->smarty->ext->_tplFunction->callTemplateFunction($_smarty_tpl, 'list', array('data'=>$_smarty_tpl->tpl_vars['entry']->value,'level'=>$_smarty_tpl->tpl_vars['level']->value+1), true);?>
</ul></li>
	<?php } else { ?>
		<li><span class="toolbar-label-list toolbar-label-info"><?php echo $_smarty_tpl->tpl_vars['number']->value;?>
</span> <b> <?php echo $_smarty_tpl->tpl_vars['entry']->key;?>
</b> - <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['entry']->value);?>
</li>
	<?php }
$_smarty_tpl->_assignInScope('number', $_smarty_tpl->tpl_vars['number']->value+1);
$_smarty_tpl->tpl_vars['entry'] = $__foreach_entry_0_saved;
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
}}
/*/ smarty_template_function_list_180549730763de40a92da6e0_39239387 */
}
