<form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
    <div class="f_left form_col">
        {form_row name='connection'}
        {form_row name='ns_pref'}
    </div>
    <div class="f_right form_col">
        {form_row name='table_regexp'}
    </div>
    {form_button text=Select}
</form>