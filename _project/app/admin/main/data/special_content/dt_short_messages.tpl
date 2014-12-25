<table class="multiRowDataTable">
    <tbody>
    {if @$isHead}
    <tr>
        {if @$showId}<th>Id</th>{/if}
        {foreach from=$columns item=col}
            <th{if @$aOpRight[$col['field']]} colspan="2"{/if}{if @$col['width']} style="width:{$col['width']}px;"{/if}>
                {if @$col['width']}
                    <img src="images/1x1.gif" width="{$col['width']}" class="headSpacer" />
                {/if}
                {if isset($hdOrder[$col['field']])}
                    {ldelim}tbl_order-{$col['field']}{rdelim}
                {else}
                    {$col['head']}
                {/if}
            </th>
        {/foreach}
        {if @$showDel}<th class="del">Del</th>{/if}
    </tr>
    {/if}
    [<tr class="row{ldelim}zebra{rdelim}">
        {if @$showId}
            <td class="id">{ldelim}id_title-id{rdelim}</td>
        {/if}
        {foreach from=$columns item=col}
            {assign var="opr" value="@$aOpRight[$col['field']]"}
            {if @$opr && $opr['pos'] != "after"}
                <td class="openRight_before">{ldelim}{$opr['pat']}-{$col['field']}{rdelim}</td>
            {/if}
            <td>{ldelim}{$col['type']}-{$col['field']}{rdelim}</td>
            {if @$opr && $opr['pos'] == "after"}
                <td class="openRight_after">{ldelim}{$opr['pat']}-{$col['field']}{rdelim}</td>
            {/if}
        {/foreach}
        {if @$showDel}
            <td class="del">{ldelim}delete_1{rdelim}</td>
        {/if}
    </tr>]
    </tbody>
</table>
