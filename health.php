<?php
include_once("../../version.php");
$conf = get_config('auth/suap');

$plugin = new stdClass();
include_once("version.php");
echo "<pre>";
echo "release: $plugin->release<br>";
echo "version: $plugin->version<br>";
echo "client_id: $conf->client_id<br>";
echo "authorize_url: $conf->authorize_url<br>";
echo "token_url: $conf->token_url<br>";
echo "rh_eu_url: $conf->rh_eu_url<br>";
echo "logout_url: $conf->logout_url<br>";
echo "</pre>";
