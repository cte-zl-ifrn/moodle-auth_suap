<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 *
 * @category    auth
 * @package     auth_suap
 */

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
