<h1>Tool for create entity</h1>
{$entity_filter}
<hr />
<form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
    {form_key_field}
    <ul>
    {foreach key=sTable item=msg from=$aTableList}
        <li>
            {if is_null($msg)}<input type="checkbox" name="tbl[{$sTable}]" value="1" id="tbl_{$sTable}" />{else}<span>&nbsp;</span>{/if}
            <label{if is_null($msg)} for="tbl_{$sTable}"{elseif $msg} class="ett_{$msg[0]} is_ett" title="{$msg[1]}"{else} class="ett_green is_ett"{/if}> {$sTable}</label>
        </li>
    {/foreach}
    </ul>

    {form_button text=Submit}
</form>
