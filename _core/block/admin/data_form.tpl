{if @$rows}
    <table class="formDataTable">
        <tbody>
        {foreach from=$rows item=row}
            <tr><td class="formDataLabel">{$row['label']}:</td><td class="formDataInput">{ldelim}{$row['type']}-{$row['field']}{rdelim}</td></tr>
        {/foreach}
        </tbody>
    </table>
{/if}