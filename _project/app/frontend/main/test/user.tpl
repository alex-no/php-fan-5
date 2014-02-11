<h1>Test user form</h1>
{if empty($user)}
    <form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
        {form_key_field}
        {form_row name='login'}
        {form_row name='password'}
        {form_button text=Submit}
    </form>
{else}
    <dl>
        <dt>Login</dt>
        <dd>{$user->getLogin()}</dd>
        <dt>First name</dt>
        <dd>{$user->getFirstName()}</dd>
        <dt>Last name</dt>
        <dd>{$user->getLastName()}</dd>
    </dl>
{/if}