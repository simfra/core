{function list level=0}{assign var='number' value=1}{foreach $data as $entry}
	{if is_array($entry)}
		<li class="toolbar-list-folding"><div><span class="toolbar-label-list toolbar-label-info">{$number}</span> <b>{$entry@key}</b><span id="plus" class="toolbar-label-list toolbar-label-success dev_toolbar_plus">+</span></div>
			<ul style="display: none;">{list data=$entry level=$level+1}</ul></li>
	{else}
		<li><span class="toolbar-label-list toolbar-label-info">{$number}</span> <b> {$entry@key}</b> - {$entry|htmlspecialchars}</li>
	{/if}{assign var='number' value=$number+1}
{/foreach}{/function}
<div id="toolbar">
	<div class="toolbar-navbar toolbar-navbar-bottom{if $minimalized_toolbar==true} toolbar-minimalizes_toolbar{else} full_toolbar{/if}">
		<ul>
			<li title="HTTP Status" class="toolbar-navbar-status {if count($dev.errors.warning)>0} devtoolbar_status_warning{elseif count($dev.errors.notice)>0}devtoolbar_status_notice font_grey{else}devtoolbar_status_ok{/if}">
				{$dev.http}
			</li>
			{if isset($debug_buffer)}
				<li>
					<i class="fas fa-exclamation-triangle"></i>
					<div class="toolbar-panel" style="min-width: 600px">
						<div class="toolbar-panel-head">
							<h6 class="toolbar-panel-title" ><i class="fas fa-exclamation-triangle"></i>Buffered output</h6>
						</div>
						<div class="toolbar-panel-body">
							<div style="padding: 15px;max-height: 400px;  line-height: 18px; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif !important; font-size: 13px; font-weight: normal; background-color: #fff; color: #000;">
								{$debug_buffer}
							</div>
						</div>
					</div>

				</li>
			{/if}
			<li class="toolbar-navbar-route">
				<i class="fa fa-tasks"></i>
				{if $ismobile!=1}<span> {$dev.lang} | {$dev.page.controller}::{$dev.page.method} </span>{/if}
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title"><i class="fa fa-tasks"></i>Application info</h6>
					</div>
					<div class="toolbar-panel-body">
						<ul>
							<li><div class="toolbar-first">Application name:</div><div class="toolbar-second">{$dev.page.app}</div></li>
							<li><div class="toolbar-first">Controller:</div><div class="toolbar-second">{$dev.page.controller}</div></li>
							<li><div class="toolbar-first">Method:</div><div class="toolbar-second">{$dev.page.method}</div></li>
							<li><div class="toolbar-first">Class:</div><div class="toolbar-second">{$dev.class_path}</div></li>
							<li><div class="toolbar-first">Route:</div><div class="toolbar-second">{$dev.page.route}</div></li>
							<li><div class="toolbar-first">Session:</div><div class="toolbar-second">{if $dev.session!=''}{$dev.session}{else}none{/if}</div></li>
							<li><div class="toolbar-first">PHP Version:</div><div class="toolbar-second">{phpversion()}</div></li>
							<li><div class="toolbar-first">Server:</div><div class="toolbar-second">{php_uname("s")} - {$smarty.server.SERVER_SOFTWARE}</div></li>
						</ul>
					</div>
				</div>
			</li>
			{if isset($logged_user)}
				<li class="toolbar-navbar-user">
					<i class="fas fa-user"></i>
					{if $ismobile!=1}<span> {$logged_user.username|ucfirst}</span>{/if}
					<div class="toolbar-panel">
						<div class="toolbar-panel-head">
							<h6 class="toolbar-panel-title" ><i class="fas fa-user"></i>User information</h6>
						</div>
						<div class="toolbar-panel-body">
							<ul>
								<li><div class="toolbar-first">User:</div><div class="toolbar-second">{$logged_user.firstname} {$logged_user.lastname}</div></li>
								<li><div class="toolbar-first">Email:</div><div class="toolbar-second">{$logged_user.email}</div></li>
								<li><div class="toolbar-first">Phone:</div><div class="toolbar-second">{$logged_user.phone}</div></li>
								<li><div class="toolbar-first">Language:</div><div class="toolbar-second">{$logged_user.language}</div></li>
								<li><div class="toolbar-first">Permissions:</div><div class="toolbar-second" style="max-width: 400px;">{implode(", ",$logged_user.permissions)}</div></li>
							</ul>
						</div>
					</div>
				</li>
			{/if}
			<li>
				<i class="far fa-clock"></i><span title="Execution time"> {$dev.time} ms</span>
			</li>
			<li>
				<i class="fas fa-save"></i><span title="Memory usage">{$dev.memory} kb</span>
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
				        {if isset($dev.database)}
						{$a=(count($dev.database.queries))}Queries: {$a}{if $ismobile!=1} ({$dev.database.time|round:"4"}ms){/if}
					{else}
						0 w (0.000 ms)
					{/if}
			</span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title" ><i class="fas fa-database"></i>List database queries</h6>
					</div>
					<div class="toolbar-panel-body  toolbar-max300">
						<ul>{foreach from=$dev.database.queries key=counter item=value}
								<li><span class="toolbar-label toolbar-label-info toolbar-small-margin">{$counter+1}</span><span class="toolbar-list-span">
									{if $value.result == true}<span class="toolbar-result_green">[ OK ]</span>{else}<span class="toolbar-result_red">[ERROR]</span>{/if}
										<span class="toolbar-db_query" title="File: {$value.query_file} - Function: {$value.query_function}{if $value.result==false}&#013;Error: {$value.error_message}{else}&#013;Number of rows in result/rows affected: {$value.rows_count}{/if}">{$value.query} - <span class="toolbar-result_green">Time: {$value.time|round:"4"} ms</span></span>
								</span></li>
							{/foreach}
						</ul>
					</div>
				</div>
			</li>
			<li>
				{$a=(count($dev.files))}
				<i class="fas fa-file-alt"></i><span title="Files" class="toolbar-label toolbar-label-info">{$a}</span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title" ><i class="fas fa-file-alt"></i>List of used files</h6>
					</div>
					<div class="toolbar-panel-body  toolbar-max300">
						<ul>{foreach from=$dev.files key=counter item=value}
								<li><span class="toolbar-label toolbar-label-info toolbar-small-margin">{$counter+1}</span><span class="toolbar-list-span">{$value}</span></li>
							{/foreach}</ul>
					</div>
				</div>
			</li>
			{$notice=count($dev.errors.notice)}
			{$warning=count($dev.errors.warning)}
			<li title="Errors">
				<i class="fas fa-cogs"></i><span class="toolbar-label toolbar-label-success"> {if isset($dev.errors)} {$notice+$warning}{else} 0 {/if}</span>
			</li>
			{if $notice>0 || $warning>0 }
				<li><span class="toolbar-navbar-error">{if $ismobile!=1}NOTICE: </span>{/if}<span class="toolbar-label toolbar-label-warning">{$notice}</span>
					{if count($dev.errors.notice)>0}
						<div class="toolbar-panel">
							<div class="toolbar-panel-head">
								<h6 class="toolbar-panel-title fas fa-cogs" ><i class="fas fa-cogs"></i>Notices</h6>
							</div>
							<div class="toolbar-panel-body toolbar-max300">
								<ul>
									{foreach from=$dev.errors.notice item=value key=key}
										<li title="File: {$value.file} - Line: {$value.line}"><span class="toolbar-label toolbar-label-warning toolbar-small-margin">{$key+1}</span><span class="toolbar-list-span">{$value.error}</span></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				</li>
				<li><span class="toolbar-navbar-error">{if $ismobile!=1}WARNING: </span>{/if}<span class="toolbar-label toolbar-label-danger">{$warning}</span>
					{if count($dev.errors.warning)>0}
						<div class="toolbar-panel">
							<div class="toolbar-panel-head">
								<h6 class="toolbar-panel-title" ><i class="fas fa-cogs"></i>Warnings</h6>
							</div>
							<div class="toolbar-panel-body toolbar-max300">
								<ul>
									{foreach from=$dev.errors.warning item=value key=key}
										<li title="File: {$value.file} - Line: {$value.line}"><span class="toolbar-label toolbar-label-danger toolbar-small-margin">{$key+1}</span><span class="toolbar-list-span">{$value.error}</span></li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				</li>
			{/if}
			<li>
				<i class="fas fa-desktop"></i><span>{if $ismobile!=1}Template Info{/if}</span>
				<div class="toolbar-panel">
					<div class="toolbar-panel-head">
						<h6 class="toolbar-panel-title" ><i class="fas fa-desktop"></i>Variables assigned in template system</h6>
					</div>
					<div class="toolbar-panel-body toolbar-max300">
						<ul id="template_vars">
							{list data=$dev_templates}
						</ul>
					</div>
				</div>
			</li>
		</ul>
	</div>
</div>