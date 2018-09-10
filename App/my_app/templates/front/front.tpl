FRONT
{$fields}
{*
{$fields->fields[0]->input}

{$fields->fields[0]->setOption("class","number")}
{$fields->fields[0]->generateView()}
*}

<pre>
{if isset($fields->errors)}
{$fields->errors|print_r}
{/if}
</pre>