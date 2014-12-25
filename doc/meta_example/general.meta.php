<?php
/**
 * Example of meta file for block
 * Note:
 *   - All paths to some other blocks set by {CONSTANTS}, which are defined in ini-file
 *   - NR - Not required parameter
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 02.014
 */
return array(
    /**
     * Meta data for embedded blocks
     */
    'common' => array(
        //<-- There is possible to set some parameters which need set in all embedded blocks
    ),
    /**
     * Meta data for current carcass block
     */
    'own' => array(
        /**
         * "embeddedBlocks","carcass","root" can be set in "own"-part only!!!
         */
        'embeddedBlocks' => array( // Key - template var; Value - path to block
            'menuTop'  => '{CAPP}/menu/main',
            'menuLeft' => '{CAPP}/menu/left',
            'main'     => '{MAIN}',
        ),
        /**
         * It is recommend to set "carcass" and "root" blocks in tab.ini as default value and in main-content block for other value
         */
        'carcass'       => 'Path to main carcass block',
        'root'          => 'Path to root block',
        'page_https'    => null OR true OR false, // NR. null: Current protocol; true: HTTPS false: HTTP. By default: action_protocol = null
        'disableSearch' => true OR false, // NR. Disable of saving page search data. By default: disableSearch = false
        'forceSearch'   => true OR false, // NR. Force of saving page search data (except role). By default: forceSearch = false
        /**
         * It is recommend to set 'title' in main-content block only
         */
        'default_view_format' => 'html' OR 'json' OR 'xml' OR 'etc', // NR. null: Define default view format

        'title'       => 'Title of page',

        'meta_tag' => array( // Meta tags
            array('name' => 'name(NR)', 'content' => 'value', 'http_equiv' => 'value(NR)', 'id' => 'id(NR)'),
            array('name' => 'name(NR)', 'content' => 'value', 'http_equiv' => 'value(NR)', 'id' => 'id(NR)'),

            array('name' => 'Keywords',    'content' => 'Keywords'),
            array('name' => 'Description', 'content' => 'Description'),
        ),

        'externalCss' => array( // css files
            'link'         => array('file1.css', 'file2.css', 'etc'), // files are attached by <link media="all">-tag
            'import'       => array('file1.css', 'file2.css', 'etc'), // files are attached by @import-directive
            'link-print'   => array('file1.css', 'file2.css', 'etc'), // files are attached by <link media="print">-tag
            'import-print' => array('file1.css', 'file2.css', 'etc'), // files are attached by @import-directive for media-print
            // Allow combine. For example:
            'import-print,projection' => array('file1.css', 'file2.css', 'etc'), // files are attached by @import-directive for media-print,projection
        ),
        'embedCss' => array(
            'all'        => 'css-text for all media',
            'aural'      => 'css-text for aural-synthesizer',
            'braille'    => 'css-text for braille-device',
            'embossed'   => 'css-text for braille-print',
            'handheld'   => 'css-text for handheld',
            'print'      => 'css-text for print',
            'projection' => 'css-text for projection',
            'screen'     => 'css-text for screen',
            'speech'     => 'css-text for aural-synthesizer',
            'tty'        => 'css-text for teletype',
            'tv'         => 'css-text for TV',
            // Allow combine. For example:
            'print,projection' => 'css-text for print and projection',
        ),

        'externalJS' => array( // JavaScript files
            'head' => array('file1.js', 'file2.js', 'etc'), // files are attached into head of html-code
            'body' => array('file1.js', 'file2.js', 'etc'), // files are attached into end of body of html-code (deprecated!!!)
        ),
        'embedJS' => array( // embed JavaScript text
            'head' => 'JavaScript-text which inserts into head of html-code',
            'body' => 'JavaScript-text which inserts into end of body of html-code',
        ),

        'template' => 'used_template_name(NR - it is need to set when template_name not equal to class_name)',
        'tpl_parent_class' => 'base template class_name', // by default is used 'template_base'


        /**
         * All parameters below it is possible to set as 'own'-part, and in 'common'-part
         */
        'tplVars' => array( // variable, which sets in template automatically
            'tplVar1' => 'Value of variable 1',
            'tplVar2' => 'Value of variable 2',
        ),
        'staticContent' => array( // Static content which is edited by admin-system
            'tplVar_1' => 'db_tpl_key1',
            'tplVar_2' => 'db_tpl_key2',
        ),
        'parseImage' => true | false, // parse Image (in the output html-code)

        'cache' => array( // cache-control parameters
            // Cache mode:
                // 0 - don't use cache there and for container,
                // 1 - don't use cache,
                // 2 - clear cache by "refrash" and "expire",
                // 3 - clear cache manualy only
            'mode'       => 0 OR 1 OR 2 OR 3,
            'expire'     => 600,           // Expired cache time
            'alwaysInit' => true OR false, // NR. Always run init. By default: alwaysInit = false

            'considerRequest' => array( // 1 - consider in cache-key, 0 - don't consider, -1 - disable cache if present
                'main'       => 0,
                'add'        => 0,
                'get'        => 0,
                'post'       => 0,
                'get:var'    => 0,
                'post:var'   => 0,
                'cookie:var' => 0,
            ),
            'considerRole' => array( // 1 - consider in cache-key, 0 - don't consider, -1 - disable cache if present
                'role_name1' => 0,
                'role_name2' => 0,
                //...
            ),

            'clear' => 'namespace', // Clear cache of other block
            // <-OR->
            'clear' => array(       // Clear cache of other blocks
                'namespace',
                'namespace',
                //...
            ),

            'entityClear' => array( // Clear cache by entity update
                'entityName-1',
                'entityName-2',
                //...
            ),
        ),

        'initOrder' => 1000, // Order to run init method for each data block
        'useMultiLanguage' => true OR false, // NR. Use multi-language operation for some procedure. By default: useMultiLanguage = false

        'roles' => array (
            array (
                'condition'    => '(role_A&!role_B)|role_C',
                'transfer_out' => 'transferURL',
            ),
            array (
                'condition'    => '(role_A|role_B)&!role_C',
                'transfer_int' => 'transferURL',
            ),
            array (
                'condition'     => '(role_A&!role_B)|role_C',
                'transfer_sham' => 'transferURL',
            ),
        ),
    ),
);
?>