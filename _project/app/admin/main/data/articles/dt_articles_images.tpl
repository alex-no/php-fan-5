{foreach from=$rows item=row}
    <div class="formDataLabel">{$row['label']}:</div>
    <div class="formDataInput">{ldelim}{$row['type']}-{$row['field']}{rdelim}</div>
{/foreach}
