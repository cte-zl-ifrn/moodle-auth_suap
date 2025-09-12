<?php
/**
 * SUAP Integration
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package     auth_suap
 * @category    upgrade
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function get_last_sort_order($tablename) {
    global $DB;
    $l = $DB->get_record_sql('SELECT coalesce(max(sortorder), 0) + 1 as sortorder from {' . $tablename . '}');
    return $l->sortorder;
}


function get_or_create($tablename, $keys, $values) {
    global $DB;
    $record = $DB->get_record($tablename, $keys);
    if (!$record) {
        $record = (object)array_merge($keys, $values);
        $record->id = $DB->insert_record($tablename, $record);
    }
    return $record;
}


function create_or_update($tablename, $keys, $inserts, $updates=[], $insert_only=[]) {
    global $DB;
    $record = $DB->get_record($tablename, $keys);
    if ($record) {
        foreach (array_merge($keys, $inserts, $updates) as $attr => $value) {
            $record->{$attr} = $value;
        }
        $DB->update_record($tablename, $record);
    } else {
        $record = (object)array_merge($keys, $inserts, $insert_only);
        $record->id = $DB->insert_record($tablename, $record);
    }
    return $record;
}


function create_setting_configtext($settings, $name, $default='') {
    $theme_name = 'auth_suap';
    $settings->add(new admin_setting_configtext("$theme_name/$name", get_string($name, $theme_name), get_string("{$name}_desc", $theme_name), $default));  
}


function create_setting_configtextarea($settings, $name, $default='') {
    $theme_name = 'auth_suap';
    $settings->add(new admin_setting_configtextarea("$theme_name/$name", get_string($name, $theme_name), get_string("{$name}_desc", $theme_name), $default));  
}


function save_user_custom_field($categoryid, $shortname, $name, $datatype = 'text', $visible = 1, $p1 = NULL, $p2 = NULL)
{
    return get_or_create(
        'user_info_field',
        ['shortname' => $shortname],
        ['categoryid' => $categoryid, 'name' => $name, 'description' => $name, 'descriptionformat' => 2, 'datatype' => $datatype, 'visible' => $visible, 'param1' => $p1, 'param2' => $p2]
    );
}
