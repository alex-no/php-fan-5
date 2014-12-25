<table class="multiRowDataTable newsSort">
    <tbody>
    {if $isHead}
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
        [<tr>
            <td class="gSeparator_1"></td>
            <td class="gSeparator_1"></td>
            {*<td class="gSeparator_1"></td>*}
            <td class="gSeparator_2"></td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="id" rowspan="2">{ldelim}id-id{rdelim}</td>
            <td class="art_page">Page № {ldelim}text_right-page_num{rdelim}</td>
            <td class="del" rowspan="2">{ldelim}delete_1{rdelim}</td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="art_content">{ldelim}wysiwyg-content_part_ru{rdelim}</td>
            {*<td class="art_content">{ldelim}wysiwyg-content_part_en{rdelim}</td>*}
        </tr>]
    </tbody>
</table>