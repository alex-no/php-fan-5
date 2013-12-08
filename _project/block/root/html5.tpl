<!DOCTYPE html>
<html lang="en">
<head>
<title>{@$title}</title>
{@$headBefore}

{*

====== External head JavaScript (IE<9) ====== *}
{nostrip}
<!--[if lt IE 9]><script type="text/javascript">
(function(){
var e='abbr,article,aside,audio,canvas,details,figure,footer,header,hgroup,mark,meter,nav,output,section,time,video'.split(',');
for(var i=0; i<e.length; i++) document.createElement(e[i]);
})();
</script><![endif]-->
{/nostrip}

{*

====== meta-tags ====== *}
{if @$meta}{foreach item=meta_item from=$meta}
<meta{=$this->makeTagAttr('name', $meta_item)} content="{$meta_item['content']}"{=$this->makeTagAttr('http-equiv', $meta_item, 'http_equiv')}{=$this->makeTagAttr('scheme', $meta_item)}{=$this->makeTagAttr('id', $meta_item)} />
{/foreach}{/if}
{*

====== Link - arbitrary value ====== *}
{if @$tagLink}{foreach item=tLnk from=$tagLink}
<link rel="{$tLnk['rel']}" type="{$tLnk['type']}" href="{$tLnk['href']}"{if $tLnk['title']} title="{$tLnk['title']}"{/if}></link>
{/foreach}{/if}
{*

====== CSS (old format) - block ====== *}
{if @$externalCss['old']}{foreach item=CssOld from=$externalCss['old']}
<link rel="stylesheet" type="text/css" href="{$CssOld}"></link>
{/foreach}{/if}
{*

====== CSS - block ====== *}
{if @$externalCss['new'] or @$embedCss}{nostrip}
<style type="text/css">
<!--
{if @$externalCss['new']}{foreach item=cssFile from=$externalCss['new']}
@import url({$cssFile});
{/foreach}{/if}
{@$embedCss}
-->
</style>
{/nostrip}{/if}
{*

====== CSS (IE) - block ====== *}
{if @$externalCss['ie']}{nostrip}
<!--[if IE]><style type="text/css">
{foreach item=cssFile from=$externalCss['ie']}
@import url({$cssFile});
{/foreach}
</style><![endif]-->
{/nostrip}{/if}
{*

====== CSS (IE<7) - block ====== *}
{if @$externalCss['ie6']}{nostrip}
<!--[if lt IE 7]><style type="text/css">
{foreach item=cssFile from=$externalCss['ie6']}
@import url({$cssFile});
{/foreach}
</style><![endif]-->
{/nostrip}{/if}
{*

====== CSS - block for print ====== *}
{if @$externalCss['print']}{nostrip}
<style type="text/css">
<!--
{foreach item=cssFile from=$externalCss['print']}
@import url({$cssFile}) print;
{/foreach}
-->
</style>
{/nostrip}{/if}
{*

====== External head JavaScript ====== *}
{if @$externalJS['head']}{foreach item=jsFile from=$externalJS['head']}
<script type="text/javascript" src="{$jsFile}"></script>
{/foreach}{/if}
{*

====== Embeded head JavaScript ====== *}
{if @$embedJS['head']}{nostrip}
<script type="text/javascript">
<!--
{@$embedJS['head'][0]}{@$embedJS['head'][1]}{@$embedJS['head'][2]}
//-->
</script>
{/nostrip}{/if}
{@$headAfter}
</head>
<!--[if gte IE 8]><body class="isIE"><![endif]--><!--[if IE 7]><body class="isIE isIE7"><![endif]--><!--[if lt IE 7]><body class="isIE isIE6"><![endif]--><!--[if !IE]>--><body{if @$bodyClass} class="{$bodyClass}"{/if}><!--<![endif]-->{if @$carcass}{$carcass}{else}{@$main}{/if}{if @$modal_win}<div id="modal_voile">&nbsp;</div>{$modal_win}{/if}
{*

====== External body JavaScript (deprecated!!!) ====== *}
{if @$externalJS['body']}{foreach item=jsFile from=$externalJS['body']}
<script type="text/javascript" src="{$jsFile}"></script>
{/foreach}{/if}
{*

====== Embeded body JavaScript (is not advisable) ====== *}
{if @$embedJS['body']}{nostrip}
<script type="text/javascript">
<!--
{@$embedJS['body'][0]}{@$embedJS['body'][1]}{@$embedJS['body'][2]}
//-->
</script>
{/nostrip}{/if}
{if @$poweredBy}{nostrip}<!--
Powered by: {$poweredBy}. Copyright (C) 2005-2014 Alexandr Nosov, http://www.alex.4n.com.ua/, Kharkov.
PHP-FAN is licensed under the terms of the GNU Lesser General Public License: http://www.opensource.org/licenses/lgpl-license.php
-->{/nostrip}{/if}
</body></html>