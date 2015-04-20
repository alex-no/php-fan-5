<?php
/*
 * Tags array
 */
return array (
  '/a' =>
  array (
    'tag' => '</a>',
  ),
  'br' =>
  array (
    'tag' => '<br/>',
  ),
  '/b' =>
  array (
    'tag' => '</b>',
  ),
  'a-contact' =>
  array (
    'tag' => '<a href="{service|tab:getURI:/contact_us.html}">',
    'link' => '/a',
    'isFunc' => true,
  ),
  'b' =>
  array (
    'tag' => '<b>',
    'link' => '/b',
  ),
  'combi_part' =>
  array (
    'tag' => '{service|translation:getCombiPart:}',
    'isFunc' => true,
  ),
  'fc-size' =>
  array (
    'tag' => '{\fan\app\frontend\main\contact_us:getMaxFileSize:}',
    'isFunc' => true,
  ),
);
?>