<?xml version="1.0" encoding="UTF-8"?>
<form>
    <name>{$form->getName()}</name>
    <id>{$form->getId()}</id>
    <class>{$form->getClass()}</class>
    <method>{$form->getMethod()}</method>
    <action>{$form->getAction()}</action>
    <submit>{$form->getSubmit()}</submit>
    <fields>
        {foreach from=$form->fields item=field}
            {$field->toXml()}
{*            <name>{$field->getName()}</name>
            <type>{$field->getClass()}</type>
            <options>
                {foreach from=$field->getOptions() item=option key=key}
                <{$key}>{$option}</{$key}>
            {/foreach}
            </options>*}
        {/foreach}
    </fields>
</form>
