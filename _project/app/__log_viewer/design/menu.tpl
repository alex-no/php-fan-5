<ul id="main_menu">
    {foreach item=menuItem from=$aMenu}
        <li{if $menuItem['key']==$sCurrent} class="current"{/if}><a href="#{$menuItem['key']}" id="{$menuItem['key']}_mn">{$menuItem['name']}</a></li>
    {/foreach}
</ul>