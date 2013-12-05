<h1>Enter your login/password</h1>
<form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
    {form_key_field}
    {form_row name='login'}
    {form_row name='password'}
    {form_button text=Submit}
</form>