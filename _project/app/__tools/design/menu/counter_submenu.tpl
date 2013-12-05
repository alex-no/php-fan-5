<table summary="sub Menu">
<tr>
    {foreach key=sKey item=menuItem from=$aMenu}
        <td{if $menuItem.key==$sCurrent} class="current"{/if}><a href="{$oBlock->getMenuUrl($menuItem['key'], $menuItem['add'])}" >{$menuItem['name']}</a></td>
    {/foreach}
</tr>
</table>