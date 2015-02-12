<nav id="main_nav">
    {foreach item=navItem from=$aNav}
        <a href="#{$navItem['key']}" id="{$navItem['key']}_mn"{if $navItem['key']==$sCurrent} class="current"{/if}>{$navItem['name']}</a>
    {/foreach}
</nav>