<table class="formDataTable" style="margin-top:20px;">
    <tbody>
    {foreach from=$rows item=row}
        {if !in_array($row['field'], $hideCol)}
            <tr><td class="formDataLabel">{$row['label']}:</td><td class="formDataInput">{ldelim}{$row['type']}-{$row['field']}{rdelim}</td></tr>
        {/if}
    {/foreach}
    </tbody>
</table>