<table class="multiRowDataTable newsSort">
    <tbody>
    {if $isHead}
    <tr>
        {foreach from=$columns item=col}
            {if isset($hdOrder[$col['field']])}<th>{ldelim}tbl_order-{$col['field']}{rdelim}</th>{/if}
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
            <td class="gSeparator_1"></td>
            <td class="gSeparator_1"></td>
            <td class="gSeparator_1"></td>
            <td class="gSeparator_1"></td>
            <td class="gSeparator_2"></td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="id" rowspan="3">{ldelim}id-id{rdelim}</td>
            <td class="news_head">{ldelim}text-header_ru{rdelim}</td>
            <td class="news_head">{ldelim}text-header_en{rdelim}</td>
            <td class="news_complete">{ldelim}checkbox-is_complete{rdelim}</td>
            <td class="news_date">{ldelim}date_clndr-news_date{rdelim}</td>
            <td class="del" rowspan="3">{ldelim}delete_1{rdelim}</td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="announce">{ldelim}wysiwyg-announcement_ru{rdelim}</td>
            <td class="announce">{ldelim}wysiwyg-announcement_en{rdelim}</td>
            <td class="news_img" colspan="2" rowspan="2">{ldelim}image_line_nail-news_image{rdelim}</td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="news_cont">{ldelim}wysiwyg-content_ru{rdelim}</td>
            <td class="news_cont">{ldelim}wysiwyg-content_en{rdelim}</td>
        </tr>]
    </tbody>
</table>