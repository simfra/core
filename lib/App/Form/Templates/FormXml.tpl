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
        <field>
            <name>{$field->getName()}</name>
            <type>{$field->getType()}</type>
            <options>
                {foreach from=$field->getOptions() item=option key=key}
                <{$key}>{$option}</{$key}>
            {/foreach}
            </options>
            <placeholder></placeholder>
            <label></label>
            <required></required>
            <rule></rule>
        </field>
        {/foreach}
    </fields>
</form>
