{if role("tools_access")}
    {if $bIsCorrect}
        <!-- Repl -->
    {else}
        <h2>Incorrect scenario name</h2>
    {/if}
{else}
    <h2>Access denied</h2>
{/if}
