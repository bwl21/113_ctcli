<?php

/**
 */

namespace CT_APITOOLS;

use Cassandra\Exception\RangeException;

$root = __DIR__ . '/..';

ini_set('memory_limit', "256M");

require 'vendor/autoload.php';

require_once __DIR__ . "/ct_apitools--helper.inc.php";

$credentialstore = "$root/private/CT-credentialstore.php";

if (!file_exists($credentialstore)) {
    file_put_contents($credentialstore, file_get_contents(__DIR__ . "/../assets/CT-credentialstore.php.template"));
    echo "\n created $credentialstore\nplease edit this file according to your site";
    echo "\n\n";
    exit(-1);
} else {
    require_once("$root/private/CT-credentialstore.php");
}

/**
 * find the scripts to be processed
 */
if (count($argv) == 1) {
    $scripts = (array)glob(__DIR__ . "/../scripts/*.php");
    $scripts = array_map('basename', $scripts);
    $scripts = join("\n", $scripts);

    echo <<<EOT
Available Scripts:

$scripts

EOT;
    exit(0);
}


if (count($argv) > 1) {
    $scripts = glob(__DIR__ . "/src/*{$argv[1]}*.php");
    if (count($scripts) == 0) {
        die("no scripts found for {$argv[1]}\n");
    }
    if (count($scripts) > 1) {
        die("more than one script found for {$argv[1]}\n");
    }
} else {
    die ("no argument given\n");
}

if (count($scripts) > 1) {
    echo "ambiguous script";
    exit(-1);
}

/**
 * login
 */

$ctdomain = CREDENTIALS['ctdomain'];
$ajax_domain = $ctdomain . "/?q=";
$email = CREDENTIALS['ctusername'];
$password = CREDENTIALS['ctpassword'];

// if no ctinstance is provided, we extract it from the ctdomain
if (!array_key_exists('ctinstance', CREDENTIALS)) {
    $x = preg_match("/https:\/\/([^\.]+)/", $ctdomain, $matches);
    $ctinstance = "{$matches[1]}_";
} else {
    $ctinstance = CREDENTIALS['ctinstance'];
}

$result = CT_loginAuth($ctdomain, $email, $password);

if (!$result['status'] == 'success') {
    var_dump($result);
    die("script aborted / login failed");
}


/**
 * execute scripts
 */

//var_dump($argv);

foreach ($scripts as $script) {

    $url = "undefined";
    $response = "undefined";
    $data = [];
    $body = [];
    $report = [];
    echo "doing $script\n";

    $scriptbase = basename($script, ".php");
    $outfolder = array_key_exists('outfolder', CREDENTIALS) ? CREDENTIALS['outfolder'] : __DIR__ . "/responses";
    $filebase = "$outfolder/{$ctinstance}_";

    // note there is no separato btween ctinstance and scriptbase
    // to support filenam built of scriptbase onley
    $outfilebase = "$filebase$scriptbase";
    require_once($script);

    $myfile = fopen("$outfilebase.json", "w") or die("Unable to open file!");
    fwrite($myfile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fclose($myfile);
    echo ("\nwrote `$outfilebase.json`\n");
}
/**
 * logout
 */


CT_logout($ajax_domain);
echo("logged out\n");