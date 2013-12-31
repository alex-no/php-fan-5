<table>
<tr>
    {foreach key=sKey item=menuItem from=$aMenu}
        <td{if $sKey==$sCurrent} class="current"{/if}><a href="{$menuItem['url']}" >{$menuItem['name']}</a></td>
    {/foreach}
</tr>
</table>