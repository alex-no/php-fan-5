{if !empty($aNavList)}
    <nav class="unfolded">
        {foreach key=id_key item=aNav1 from=$aNavList name=main_nav }
            <a href="{$aNav1['url_value']}" class="{$this->getLnkClass($aNav1, 'main_nav')}"{$this->makeTagAttr('target', $aNav1)}>
                {$aNav1['nav_name']}
            </a>
        {/foreach}
    </nav>
{/if}
{method: protected function getLnkClass($Var, $sName)
{
    return ((@$Var['current'] || @$Var['catalog_current']) ? 'current' : '') . ($this->isFirst($sName) ? ' first' : '') . ($this->isLast($sName) ? ' last' : '');
}
endmethod}