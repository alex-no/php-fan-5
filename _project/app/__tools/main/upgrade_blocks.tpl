<h1>Tool for upgrade blocks from PHP-FAN4 to PHP-FAN5</h1>
<ul>
    <li>Namespace added: {=implode(' / ', $aNsAdded)} (added / exists / error)</li>
    <li>Converted "extended": {=implode(' / ', $aExtSet)} (converted / exists / error)</li>
    <li>Converted "service": {=implode(' / ', $aServiceCalls)} (converted / error / both)</li>
    <li>Converted "entity": {=implode(' / ', $aEntitySet)} (converted / error / both)</li>
    <li>Direct replaced: {=implode(' / ', $aDirReplace)} (php / meta / tpl)</li>
    <li>Set final coment: {=implode(' / ', $aFinalComent)} (set / exists / error)</li>
    {*<li></li>*}
</ul>

<br />
<br />
<br />
<br />

Total changed files:
<ul>
    <li>php:  {$aChanged['php']}</li>
    <li>meta: {$aChanged['meta']}</li>
    <li>tpl:  {$aChanged['tpl']}</li>
<br />
<br />

Not writeble files:
<ul>
    <li>php:  {$aNotWr['php']}</li>
    <li>meta: {$aNotWr['meta']}</li>
    <li>tpl:  {$aNotWr['tpl']}</li>
</ul>