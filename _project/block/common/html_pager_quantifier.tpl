<form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
    {form_key_field}
    {foreach key=key item=value from=$_quantifier_params}
        <input type="hidden" name="{$key}" value="{$value}" />
    {/foreach}
    {form_row name='pager_quantifier'}
    <button type="submit"><span>set</span></button>
</form>