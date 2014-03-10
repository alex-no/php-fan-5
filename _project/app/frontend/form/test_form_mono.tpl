<form action="{$action_url}" id="{$form_id}" name="{$form_id}" method={$action_method}>
    {form_key_field}
    {form_row name='text1'}
    <div class="formRow">{form_label name='date'}{form_field name='date'}{form_error name='date'}{form_note name='date'}</div>
    {form_row name='variant'}
    {form_button text=Submit}
</form>

{if !empty($all_data) && !empty($separate_data)}
    <br />
    <dl class="result">
        <dt>Full list of data</dt>
            <dd><pre>{$all_data}</pre></dd>
        <dt>Separate data</dt>
            <dd><pre>{$separate_data}</pre></dd>
    </dl>
{/if}