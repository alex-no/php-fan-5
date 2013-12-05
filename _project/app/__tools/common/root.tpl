<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>{@$title}</title>
{@$headBefore}
{*

====== meta-tags ====== *}
{if @$meta}{foreach item=meta_item from=$meta}
<meta{=$this->makeTagAttr('name', $meta_item)} content="{$meta_item['content']}"{=$this->makeTagAttr('http-equiv', $meta_item, 'http_equiv')}{=$this->makeTagAttr('scheme', $meta_item)}{=$this->makeTagAttr('id', $meta_item)} />
{/foreach}{/if}
{*

====== CSS - block ====== *}
{if @$externalCss['new'] or @$embedCss}{nostrip}
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/
{if @$externalCss['new']}{foreach item=cssFile from=$externalCss['new']}
@import url({$cssFile});
{/foreach}{/if}
{@$embedCss}
/*]]>*/-->
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
<!--//--><![CDATA[//><!--
{@$embedJS['head'][0]}{@$embedJS['head'][1]}{@$embedJS['head'][2]}
//--><!]]>
</script>
{/nostrip}{/if}
{@$headAfter}
</head>
<body>{if @$carcass}{$carcass}{else}{@$mainContent}{/if}
{*

====== Embeded body JavaScript (is not advisable) ====== *}
{if @$embedJS['body']}{nostrip}
<script type="text/javascript">
<!--//--><![CDATA[//><!--
{@$embedJS['body'][0]}{@$embedJS['body'][1]}{@$embedJS['body'][2]}
//--><!]]>
</script>
{/nostrip}{/if}
{if @$poweredBy}{nostrip}<!--
Powered by: {$poweredBy} Copyright (C) 2005-2012 Alexandr Nosov, http://www.alex.4n.com.ua/, Kharkov.
PHP-FAN is licensed under the terms of the GNU Lesser General Public License: http://www.opensource.org/licenses/lgpl-license.php
-->{/nostrip}{/if}
</body></html>