<h1>Tool of updating DB by dumps</h1>

{if $isCorrect}
    <form action="{uri '/db_up/scenario_processing.html'}" id="{$form_id}" name="{$form_id}" method={$action_method} target="scenario_processing">
        {form_row name='scenario'}

        <button type="submit">Start process</button>
    </form>

    <iframe src="about:blank" name="scenario_processing" id="scenario_processing"></iframe>
{else}
    <p>There aren't files of scenario there: <b>{$sDirPath}</b></p>
{/if}