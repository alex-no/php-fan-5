#!/usr/bin/php
<?php
$bIsError = false;
$sRootDir = realpath(__DIR__ . '/..');

// ------- Make required directories ------- \\
makeReqDir(array(
    'logs'      => array(
        'apache_log' => null,
        'bootstrap_log' => null,
        'data_log'      => null,
        'error_log'     => null,
        'message_log'   => null,
    ),
    'temp_data' => array(
        'cache' => array(
            'blocks'     => null,
            'common'     => null,
            'config'     => null,
            'entity'     => null,
            'file_store' => null,
            'img_nail'   => null,
            'template'   => null,
        ),
        'file_data' => array(
            'file_info'  => null,
            'image_nail' => null,
        ),
        'nail'  => null,
        'other' => null,
    ),
), $sRootDir);

// ------- Make Apache conf ------- \\
$sDomain = end($argv);
if (substr($sDomain, -11) != 'install.php') {
    makeApacheConf($sRootDir, $sDomain);
} else {
    echo 'If you would like to make the config-file for Virtual host of the Apache, please run this file with domain: "install.php your.test.domain"' . "\n";
}

// ------- Finish ------- \\
if ($bIsError) {
    echo 'Process is finished with error.';
    sleep(7);
} else {
    echo 'Process is finished successfully.';
    sleep(2);
}

// ============== Local functions ============== \\
/**
 * Make required directories
 * @param array $aStruct
 * @param string $sParentzDir
 * @param numeric $nLevel
 */
function makeReqDir($aStruct, $sParentDir, $nLevel = 0)
{
    global $bIsError;
    foreach ($aStruct as $k => $v) {
        $sDir = $sParentDir . '/' . $k;

        if (!checkDir($sDir, $nLevel > 0 ? 0766 : 0666, $nLevel > 0)) {
            continue;
        }

        if (!empty($v) && is_array($v)) {
            makeReqDir($v, $sDir, $nLevel + 1);
        }
    }
} // function makeReqDir

/**
 * Make directory structure
 * @param string $sParentzDir
 * @param string $sDomain
 */
function makeApacheConf($sRootDir, $sDomain)
{
    global $bIsError;
    $sConfDir = $sRootDir . '/httpd_conf';
    if (!checkDir($sConfDir, 0766)) {
        return;
    }

    $sConfFile = $sConfDir . '/' . str_replace('.', '_', $sDomain) . '.conf';
    for ($i = 0; $i < 20; $i++) {
        if (!is_file($sConfFile)) {
            break;
        }
        $sConfFile = substr($sConfFile, 0, -5) . '[' . $i . '].conf';
    }
    if (substr($sDomain, 0, 4) == 'www.') {
        $sDomain = substr($sDomain, 4);
    }
    $sRootDir = str_replace('\\', '/', $sRootDir);
    $sContent = '<VirtualHost *:80>
    ServerAdmin admin@' . $sDomain . '
    DocumentRoot "' . $sRootDir . '/htdocs"
    ServerAlias www.' . $sDomain . '
    ServerName ' . $sDomain . '

    ErrorLog ' . $sRootDir . '/logs/apache_log/error.log
    CustomLog ' . $sRootDir . '/logs/apache_log/access.log common

    <Directory ' . $sRootDir . '/htdocs>
        Options Indexes FollowSymLinks
        AllowOverride All
        DirectoryIndex index.php index.html index.htm
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>';

    file_put_contents($sConfFile, $sContent);
    echo 'Config file for Apache is saved to: ' . $sConfFile . "\n";
} // function makeApacheConf

function checkDir($sDir, $nMode, $bWrRequired = true)
{
    global $bIsError;
    // ToDo: Checking of the privileges is incorrect for x-nix system. It is need to check web-server as owner directories
    if (is_file($sDir)) {
       echo 'Error! Can\'t create directory. Such file exists there: ' . $sDir . "\n";
       $bIsError = true;
       return false;
    } elseif (!is_dir($sDir)) {
       echo 'Make directory: ' . $sDir . "\n";
       if (!mkdir($sDir, $nMode)) {
           echo 'Error! Can\'t create directory: ' . $sDir . "\n";
           $bIsError = true;
           return false;
       }
    } elseif ($bWrRequired && !is_writable($sDir)) {
       echo 'Error! Directory isn\'t writable: ' . $sDir . "\n";
       $bIsError = true;
       return false;
    }
    return true;
} // function checkDir
?>