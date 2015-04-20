<table class="multiRowDataTable newsSort">
    <tbody>
    {if @$isHead}
    <tr>
        {foreach from=$columns item=col}
            <th>{if isset($hdOrder[$col['field']])}{ldelim}tbl_order-{$col['field']}{rdelim}{else}{$col['head']}{/if}</th>
        {/foreach}
    </tr>
    {/if}
    </tbody>
</table>
<table class="multiRowDataTable">
    <tbody>
        [<tr class="row{ldelim}zebra{rdelim}">
            <td colspan="2" class="msg_file">{ldelim}not_edit-file{rdelim}</td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="msg_stage">{ldelim}not_edit-stage{rdelim}</td>
            <td class="msg_url">{ldelim}not_edit-url{rdelim}</td>
        </tr>]
    </tbody>
</table>