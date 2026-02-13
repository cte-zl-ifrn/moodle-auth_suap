<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     auth_suap
 * @category    string
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SUAP OAuth2 Authentication';
$string['auth_suap_description'] = 'SUAP is the Unified System of Public Administration (Sistema Unificado de Administração Pública) used throughout Brazilian federal institutions, including the Federal Institute of Rio Grande do Norte (IFRN). This plugin enables single sign-on (SSO) integration, allowing students and staff to log into Moodle using their SUAP credentials. It automatically synchronizes user data from SUAP (name, email, CPF, enrollment status) and supports role-based access control based on institutional data.';
$string['auth_description'] = 'OAuth2 Authentication';

$string['client_id'] = 'OAuth2 Client ID';
$string['client_id_desc'] = "Obtain from SUAP: Technology Management > Services > OAuth2 Applications. Register your Moodle instance with authorization type 'Authorization code' (public client) and set redirect URI to: {$CFG->wwwroot}/auth/suap/authenticate.php";

$string['client_secret'] = 'OAuth2 Client Secret';
$string['client_secret_desc'] = "This secret is displayed only once when you create the OAuth2 application in SUAP. Save it immediately as it cannot be retrieved later. To generate a new secret, register a new application in SUAP.";

$string['authorize_url'] = 'SUAP Authorization Endpoint';
$string['authorize_url_desc'] = "SUAP OAuth2 authorization URL (typically https://suap.ifrn.edu.br/o/authorize/)";

$string['token_url'] = 'SUAP Token Endpoint';
$string['token_url_desc'] = "SUAP OAuth2 token exchange URL (typically https://suap.ifrn.edu.br/o/token/)";

$string['rh_eu_url'] = 'SUAP RH/EU API Endpoint';
$string['rh_eu_url_desc'] = "SUAP API endpoint for retrieving user identification and personal documents data (typically https://suap.ifrn.edu.br/api/eu/)";

$string['logout_url'] = 'SUAP Logout URL';
$string['logout_url_desc'] = "SUAP logout endpoint for session termination (typically https://suap.ifrn.edu.br/o/logout/)";
