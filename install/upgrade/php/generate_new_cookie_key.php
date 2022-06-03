<?php

// require_once (_PS_TOOL_DIR_.'defuse/php-encryption/defuse-crypto.phar');

function generate_new_cookie_key()
{
    $_SETTINGS_FILE = 'config/settings.inc.php';
    $root_dir = realpath(__DIR__.'/../../../../../../');
    
    require_once $root_dir.'/tools/random_compat/lib/random.php';
    $key = \Defuse\Crypto\Key::createNewRandomKey();
    $new_cookie_key = $key->saveToAsciiSafeString();
    
    // if (file_exists($root_dir.'/'.$_SETTINGS_FILE)) {
    //     $tmp_settings = file_get_contents($root_dir.'/'.$_SETTINGS_FILE);
    // } else {
    //     $tmp_settings = null;
    // }
    $fd = fopen($root_dir.'/'.$_SETTINGS_FILE, 'a');
    fwrite($fd, "define('_NEW_COOKIE_KEY_', '$new_cookie_key');" . PHP_EOL);
    fclose($fd);

    return true;
}