<?php
/**
 * SUAP Integration
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package     auth_suap
 * @category    auth
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/locallib.php');

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_suap/pluginname', '', get_string('auth_suap_description', 'auth_suap')));

    $suap_base_url = getenv('SUAP_BASE_URL') ?: 'https://suap.ifrn.edu.br';

    create_setting_configtext($settings, "client_id", "veja no SUAP");
    create_setting_configtext($settings, "client_secret", "veja no SUAP");
    create_setting_configtext($settings, "authorize_url", "$suap_base_url/o/authorize/");
    create_setting_configtext($settings, "token_url", "$suap_base_url/o/token/");
    create_setting_configtext($settings, "rh_eu_url", "$suap_base_url/api/rh/eu/");
    create_setting_configtext($settings, "logout_url", "$suap_base_url/comum/logout/");

    $authplugin = get_auth_plugin('suap');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields, get_string('auth_fieldlocks_help', 'auth'), true, true, $authplugin->get_custom_user_profile_fields());
}
