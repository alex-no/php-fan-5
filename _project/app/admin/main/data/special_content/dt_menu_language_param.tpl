<table class="multiRowDataTable">
    <tbody>
    <tr>
        {if $showId}<th>Id</th>{/if}
        {foreach from=$columns item=col}
            {if !in_array($col['field'], $hideCol)}
                <th{if @$col['width']} style="width:{@$col['width']}px;"{/if}>
                    {if @$col['width']}
                        <img src="images/1x1.gif" width="{$col[width]}" class="headSpacer" />
                    {/if}
                    {if isset($hdOrder[$col['field']])}
                        {ldelim}tbl_order-{$col['field']}{rdelim}
                    {else}
                        {$col['head']}
                    {/if}
                </th>
            {/if}
        {/foreach}
        {if $showDel}<th class="del">Del</th>{/if}
    </tr>
    [<tr class="row{ldelim}zebra{rdelim}">
        {if $showId}
            <td class="id">{ldelim}id-id{rdelim}</td>
        {/if}
        {foreach from=$columns item=col}
            {if !in_array($col['field'], $hideCol)}
                <td>{ldelim}{$col['type']}-{$col['field']}{rdelim}</td>
            {/if}
        {/foreach}
        {if $showDel}
            <td class="del">{ldelim}delete_1{rdelim}</td>
        {/if}
    </tr>]
    </tbody>
</table>