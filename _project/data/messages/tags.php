<?php
/*
 * Tags array
 */
return array (
  '/a' => 
  array (
    'tag' => '</a>',
  ),
  '/b' => 
  array (
    'tag' => '</b>',
  ),
  'a-contact' => 
  array (
    'tag' => '<a href="{\project\service\tab:getStaticUrl:/contact_us.html}">',
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
    'tag' => '{\project\service\translation:getCombiPart:}',
    'isFunc' => true,
  ),
  'fc-size' => 
  array (
    'tag' => '{\app\frontend\main\contact_us:getMaxFileSize:}',
    'isFunc' => true,
  ),
);
?>