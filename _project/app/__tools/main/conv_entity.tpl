<h1>Enter your login/password</h1>
<form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
    {form_key_field}
    {form_row name='source_dir'}
    {form_row name='source_mask'}
    {form_row name='dest_dir'}
    {form_button text=Submit}
</form>