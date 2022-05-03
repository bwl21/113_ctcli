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
 * find the showcases to be processed
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
    $scripts = glob("scripts/*{$argv[1]}*.php");
}

if (empty($scripts)) {
    echo "no script found for '{$argv[1]}'";
    exit(-1);
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
    die("Showcase aborted / login failed");
}

/**
 * execute showcases
 */

foreach ($scripts as $script) {

    $url = "undefined";
    $response = "undefined";
    $data = [];
    $body = [];
    $report = [];
    echo "doing $script\n";

    $showcasebase = basename($script, ".php");
    $outfolder = array_key_exists('outfolder', CREDENTIALS) ? CREDENTIALS['outfolder'] : ".";
    $outfolder = "$root/workdir/$outfolder";

    if (!is_dir($outfolder)) {
        echo("\ncreating $outfolder");
        mkdir($outfolder, 0777, true);
    }

    // clear outfolder

    array_map('unlink', array_filter((array)glob("$outfolder/*")));

    // note there is no separator between ctinstance and showcasebase
    // to support filename built of showcasebase only (without mentioning the ctinstance)
    $outfilebase = "$outfolder/{$ctinstance}$showcasebase";
    require_once($script);
    echo($outfilebase);
    $myfile = fopen("$outfilebase.json", "w") or die("Unable to open file!");
    fwrite($myfile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fclose($myfile);
}
/**
 * logout
 */

CT_logout($ajax_domain);
