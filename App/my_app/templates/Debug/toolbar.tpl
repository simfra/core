{function list level=0}{assign var='number' value=1}{foreach $data as $entry}
    {if is_array($entry)}
        <li class="list-folding"><div><span class="label-list label-info">{$number}</span> <b>{$entry@key}</b><span class="label-list label-success dev_toolbar_plus">+</span></div>
        <ul style="display: none;">{list data=$entry level=$level+1}</ul></li>
    {else}
        <li><span class="label-list label-info">{$number}</span> <b> {$entry@key}</b> - {$entry|htmlspecialchars}</li>
    {/if}{assign var='number' value=$number+1}
{/foreach}{/function}



<div class="navbar navbar-bottom{if $minimalized_toolbar==true} minimalizes_toolbar{else} full_toolbar{/if}">
	<ul>
		<li title="HTTP Status" class="navbar-status {if count($dev.errors.warning)>0} devtoolbar_status_warning{elseif count($dev.errors.notice)>0}devtoolbar_status_notice font_grey{else}devtoolbar_status_ok{/if}">
			{$dev.http}
		</li>
		{if isset($debug_buffer)}
			<li>
				<i class="fas fa-exclamation-triangle"></i>
				<div class="panel" style="min-width: 600px">
					<div class="panel-head">
						<h6 class="panel-title" data-icon="&#xf0c9;">Buffered output</h6>
					</div>
					<div class="panel-body">
						<div style="padding: 15px;max-height: 400px;  line-height: 18px; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif !important; font-size: 13px; font-weight: normal; background-color: #fff; color: #000;">
							{$debug_buffer}
						</div>
					</div>
				</div>		{*
							<div class="cell" style="width: 40px; height: 40px; ">
								<div id="buf" title="Buffered output" style="float: left;width: 40px;height: 40px; position: absolute; top: 0; left: 0px;background-color: #333;line-height: 40px;text-align: center; color: #fff;" class="fas fa-exclamation-triangle"></div>
							</div>


							<div class="cell" style="width: auto;display: none" id="polo">
								<div class="panel"> {*style="max-height: 400px;  line-height: 18px; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif !important; font-size: 13px; font-weight: normal; background-color: #fff; color: #000;"> *}
{*									<div class="panel-head" style="border-right: 2px solid #333;">
										<h6 class="panel-title">Buffered output (size: {strlen($debug_buffer)})</h6>
									</div>
									<div class="panel-body h400 border-grey">
										<div style="padding: 15px;max-height: 400px;  line-height: 18px; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif !important; font-size: 13px; font-weight: normal; background-color: #fff; color: #000;">
											{$debug_buffer}
										</div>
									</div>
								</div>
							</div>*}

			</li>
		{/if}
		<li class="navbar-route">
			<i class="fa fa-tasks"></i>
			{if $ismobile!=1}<span> {$dev.lang} | {$dev.page.controller}::{$dev.page.method} </span>{/if}
			<div class="panel">
				<div class="panel-head">
					<h6 class="panel-title" data-icon="&#xf0c9;">Application info</h6>
				</div>
				<div class="panel-body">
					<ul>
						<li><div class="first">Application name:</div><div class="second">{$dev.page.app}</div></li>
						<li><div class="first">Controller:</div><div class="second">{$dev.page.controller}</div></li>
						<li><div class="first">Method:</div><div class="second">{$dev.page.method}</div></li>
						<li><div class="first">Class:</div><div class="second">{$dev.class_path}</div></li>
						<li><div class="first">Route:</div><div class="second">{$dev.page.route}</div></li>
						<li><div class="first">Session:</div><div class="second">{if $dev.session!=''}{$dev.session}{else}none{/if}</div></li>
					</ul>
				</div>
			</div>
		</li>
		<li>
			<i class="far fa-clock"></i><span title="Execution time"> {$dev.time} ms</span>
		</li>
		<li>
			<i class="fas fa-save"></i><span title="Memory usage">{$dev.memory} kb</span>
		</li>
		<li>
			<i class="fas fa-retweet"></i><span class="label label-info" data-count="0">0</span>
			<div class="panel">
				<div class="panel-head">
					<h6 class="panel-title" data-icon="&#xf079;">Ajax requests</h6>
				</div>
				<div class="panel-body">
					<ul>
						<li><div class="first">Application name:</div><div class="second">{$dev.page.app}</div></li>
						<li><div class="first">Controller:</div><div class="second">{$dev.page.controler}</div></li>
						<li><div class="first">Method:</div><div class="second">{$dev.page.method}</div></li>
						<li><div class="first">Class:</div><div class="second">{$dev.class_path}</div></li>
						<li><div class="first">Route:</div><div class="second">{$dev.page.route}</div></li>
						<li><div class="first">Session:</div><div class="second">{if $dev.session!=''}{$dev.session}{else}brak{/if}</div></li>
					</ul>
				</div>
			</div>
		</li>
		<li>
			<i class="fas fa-database"></i>
			<span title="Database queries">
			{if isset($dev.database)}
						{$a=(count($dev.database)-1)}{$a}{if $ismobile!=1} ({$dev.database.time}ms){/if}
			{else}
			0 w (0.000 ms)
			{/if}
			</span>
		</li>
		<li>
			{$a=(count($dev.files))}
			<i class="fas fa-file-alt"></i><span title="Files" class="label label-info">{$a}</span>
			<div class="panel">
				<div class="panel-head">
					<h6 class="panel-title" data-icon="&#xf15c;">List of used files</h6>
				</div>
				<div class="panel-body  max300">
					<ul>{foreach from=$dev.files key=counter item=value}
						<li><span class="label label-info small-margin">{$counter+1}</span><span class="list-span">{$value}</span></li>
 			{/foreach}</ul>
				</div>
			</div>
		</li>
		{$notice=count($dev.errors.notice)}
		{$warning=count($dev.errors.warning)}
		<li title="Errors">
			<i class="fas fa-cogs"></i><span class="label label-success"> {if isset($dev.errors)} {$notice+$warning}{else} 0 {/if}</span>
		</li>
		{if $notice>0 || $warning>0 }
		<li><span class="navbar-error">{if $ismobile!=1}NOTICE: </span>{/if}<span class="label label-warning">{$notice}</span>
			{if count($dev.errors.notice)>0}
			<div class="panel">
				<div class="panel-head">
					<h6 class="panel-title fas fa-cogs" data-icon="&#xf085;">Notices</h6>
				</div>
				<div class="panel-body max300">
					<ul>
						{foreach from=$dev.errors.notice item=value key=key}
							<li title="File: {$value.file} - Line: {$value.line}"><span class="label label-warning small-margin">{$key+1}</span><span class="list-span">{$value.error}</span></li>
						{/foreach}
					</ul>
				</div>
			</div>
			{/if}
		</li>
		<li><span class="navbar-error">{if $ismobile!=1}WARNING: </span>{/if}<span class="label label-danger">{$warning}</span>
			{if count($dev.errors.warning)>0}
				<div class="panel">
					<div class="panel-head">
						<h6 class="panel-title" data-icon="&#xf085;">Warnings</h6>
					</div>
					<div class="panel-body max300">
						<ul>
							{foreach from=$dev.errors.warning item=value key=key}
								<li title="File: {$value.file} - Line: {$value.line}"><span class="label label-danger small-margin">{$key+1}</span><span class="list-span">{$value.error}</span></li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/if}
		</li>
		{/if}
		<li>
			<i class="fas fa-desktop"></i><span>{if $ismobile!=1}Template Info{/if}</span>
			<div class="panel">
				<div class="panel-head">
					<h6 class="panel-title" data-icon="&#xf15c;">Variables assigned in template system</h6>
				</div>
				<div class="panel-body max300">
					<ul id="template_vars">
						{list data=$dev_templates}
					</ul>


{*                        {foreach from=$dev_templates key=key item=value}
						<li>
							<span>
								{if !is_array($value)}
								<b>{$key}</b> - {$value}
								{else}
								<ul>
									{foreach from=$value key=key2 item=value2}
										<li>
											<span>
												{if !is_array($value2)}
												<b>{$key2}</b> - {$value2}
												{else}

												{/if}
											</span>
										</li>
									{/foreach}
								</ul>
								{/if}
							</span>
						</li>
						{/foreach}*}
{*                        {$dev_templates} *}
				</div>
			</div>
		</li>
	</ul>
</div>
