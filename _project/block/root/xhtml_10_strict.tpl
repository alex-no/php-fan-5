{if $browserType eq "normal"}<?xml version="1.0" encoding="UTF-8"?>{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>{@$title}</title>
{$headBefore}
{*

====== CSS - block ====== *}
{if $meta}{foreach item=meta_item from=$meta}
<meta{if $meta_item.name} name="{$meta_item.name}"{/if} content="{$meta_item.content}"{if $meta_item.http_equiv} http-equiv="{$meta_item.http_equiv}"{/if}{if $meta_item.scheme} scheme="{$meta_item.scheme}"{/if}{if $meta_item.id} id="{$meta_item.id}"{/if} />
{/foreach}{/if}
{*

====== Link - arbitrary value ====== *}
{if $tagLink}{foreach item=tLnk from=$tagLink}
<link rel="{$tLnk.rel}" type="{$tLnk.type}" href="{$tLnk.href}"{if $tLnk.title} title="{$tLnk.title}"{/if}></link>
{/foreach}{/if}
{*

====== CSS (old format) - block ====== *}
{if $externalCss.old}{foreach item=CssOld from=$externalCss.old}
<link rel="stylesheet" type="text/css" href="{$CssOld}"></link>
{/foreach}{/if}
{*

====== CSS - block ====== *}
{if $externalCss.new or $embedCss}
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/
{if $externalCss.new}{foreach item=cssFile from=$externalCss.new}
@import url({$cssFile});
{/foreach}{/if}
{$embedCss}
/*]]>*/-->
</style>
{/if}
{*

====== CSS (IE) - block ====== *}
{if $externalCss.ie}
<!--[if IE]><style type="text/css">
{foreach item=cssFile from=$externalCss.ie}
@import url({$cssFile});
{/foreach}
</style><![endif]-->
{/if}
{*

====== CSS (IE<7) - block ====== *}
{if $externalCss.ie6}
<!--[if lt IE 7]><style type="text/css">
{foreach item=cssFile from=$externalCss.ie6}
@import url({$cssFile});
{/foreach}
</style><![endif]-->
{/if}
{*

====== External head JavaScript ====== *}
{if $externalJS.head}{foreach item=jsFile from=$externalJS.head}
<script type="text/javascript" src="{$jsFile}"></script>
{/foreach}{/if}
{*

====== Embeded head JavaScript ====== *}
{if $embedJS.head}
<script type="text/javascript">
<!--//--><![CDATA[//><!--
{$embedJS.head[0]}{$embedJS.head[1]}{$embedJS.head[2]}
//--><!]]>
</script>
{/if}
{$headAfter}
</head>
<!--[if gte IE 8]><body class="isIE"><![endif]--><!--[if IE 7]><body class="isIE isIE7"><![endif]--><!--[if lt IE 7]><body class="isIE isIE6"><![endif]--><!--[if !IE]>--><body{if $bodyClass} class="{$bodyClass}"{/if}><!--<![endif]-->{if @$carcass}{$carcass}{else}{@$main}{/if}{if $modal_win}<div id="modal_voile">&nbsp;</div>{$modal_win}{/if}
{*

====== External body JavaScript (deprecated!!!) ====== *}
{if $externalJS.body}{foreach item=jsFile from=$externalJS.body}
<script type="text/javascript" src="{$jsFile}"></script>
{/foreach}{/if}
{*

====== Embeded body JavaScript (is not advisable) ====== *}
{if $embedJS.body}
<script type="text/javascript">
<!--//--><![CDATA[//><!--
{$embedJS.body[0]}{$embedJS.body[1]}{$embedJS.body[2]}
//--><!]]>
</script>
{/if}
{if @$poweredBy}{nostrip}<!--
Powered by: {$poweredBy}. Copyright (C) 2005-2011 Alexandr Nosov, http://www.alex.4n.com.ua/, Kharkov.
PHP-FAN is licensed under the terms of the GNU Lesser General Public License: http://www.opensource.org/licenses/lgpl-license.php
-->{/nostrip}{/if}
</body></html>