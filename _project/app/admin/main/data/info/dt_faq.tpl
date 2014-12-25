{if $isHead}
    <table class="multiRowDataTable" style="width:685px;">
        <tbody>
            <tr>
                {foreach from=$columns item=col}
                    {if @$col['head']}<th>{if isset($hdOrder[$col['field']])}{ldelim}tbl_order-{$col['field']}{rdelim}{else}{$col['head']}{/if}</th>{/if}
                {/foreach}
            </tr>
        </tbody>
    </table>
{/if}
<table class="multiRowDataTable">
    <tbody>
        [<tr>
            <td colspan="8" style="font-size:1px;height:4px;"></td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="id">{ldelim}id-id{rdelim}</td>
            <td class="order_key" title="Порядок вывода">{ldelim}text_right-order_key{rdelim}</td>
            <td class="member_name" title="Пользователь задавший вопрос">{ldelim}not_edit-__member_name{rdelim}</td>
            <td title="email задавшего вопрос">{ldelim}not_edit-__email{rdelim}</td>
            <td title="ip-address задавшего вопрос">{ldelim}not_edit-ip_adr{rdelim}</td>
            <td title="дата/время задания вопроса">{ldelim}not_edit-dt_time{rdelim}</td>
            <td title="тип вопроса">{ldelim}select_hidden-id_faq_type{rdelim}</td>
            <td class="del" rowspan="3">{ldelim}delete_1{rdelim}</td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="id">Вопрос</td>
            <td class="quest" colspan="3">{ldelim}textarea-question_ru{rdelim}</td>
            <td class="quest" colspan="2">{ldelim}textarea-question_en{rdelim}</td>
            <td rowspan="2">{ldelim}image_line_nail-faq_image{rdelim}</td>
        </tr>
        <tr class="row{ldelim}zebra{rdelim}">
            <td class="id">Ответ</td>
            <td class="answer" colspan="3">{ldelim}wysiwyg-answer_ru{rdelim}</td>
            <td class="answer" colspan="2">{ldelim}wysiwyg-answer_en{rdelim}</td>
        </tr>]
    </tbody>
</table>