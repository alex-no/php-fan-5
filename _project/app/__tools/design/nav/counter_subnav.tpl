<table>
    <tr><td><nav>
    {foreach key=sKey item=navItem from=$aNav}
        <a href="{$oBlock->getNavUrl($navItem['key'], $navItem['add'])}"{if $navItem.key==$sCurrent} class="current"{/if}>{$navItem['name']}</a>
    {/foreach}
    </nav></td></tr>
</table>