<table>
    <tr><td><nav>
    {foreach key=sKey item=navItem from=$aNav}
        <a href="{$navItem['url']}"{if $sKey==$sCurrent} class="current"{/if}>{$navItem['name']}</a>
    {/foreach}
    </nav></td></tr>
</table>