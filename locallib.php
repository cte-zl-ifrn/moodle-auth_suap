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
 * @category   auth
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Make HTTP POST request using cURL
 *
 * @param string $url URL to post to
 * @param array|string $data Data to send (array will be form-encoded, string sent as-is)
 * @param string $contenttype Content-Type header (default: application/x-www-form-urlencoded)
 * @param array $headers Additional headers
 * @return string Response body
 */
function auth_suap_curl_post($url, $data, $contenttype = 'application/x-www-form-urlencoded', $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    // Force HTTP/1.1 instead of HTTP/2
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    
    // Prepare data based on content type.
    if ($contenttype === 'application/x-www-form-urlencoded' && is_array($data)) {
        // Use PHP_QUERY_RFC3986 to encode with & instead of &amp;
        $postdata = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    } else if ($contenttype === 'application/json' && is_array($data)) {
        $postdata = json_encode($data);
    } else {
        $postdata = $data;
    }
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    
    // Set headers with explicit Content-Type and Content-Length.
    $httpheaders = $headers;
    if ($contenttype === 'application/x-www-form-urlencoded') {
        $httpheaders[] = 'Content-Type: application/x-www-form-urlencoded';
        $httpheaders[] = 'Content-Length: ' . strlen($postdata);
    } else {
        $httpheaders[] = 'Content-Type: ' . $contenttype;
    }
    
    if (!empty($httpheaders)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
    }
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlerror = curl_errno($ch);
    $curlerrormsg = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlerror) {
        throw new Exception('cURL error: ' . $curlerrormsg . ' (code: ' . $curlerror . ')');
    }
    
    if ($httpcode >= 400) {
        throw new Exception('HTTP error ' . $httpcode . ': ' . substr($response, 0, 500));
    }
    
    return $response;
}

/**
 * Make HTTP GET request using cURL
 *
 * @param string $url URL to get
 * @param array $headers Additional headers
 * @return string Response body
 */
function auth_suap_curl_get($url, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlerror = curl_errno($ch);
    $curlerrormsg = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlerror) {
        throw new Exception('cURL error: ' . $curlerrormsg . ' (code: ' . $curlerror . ')');
    }
    
    if ($httpcode >= 400) {
        throw new Exception('HTTP error ' . $httpcode . ': ' . substr($response, 0, 500));
    }
    
    return $response;
}

function auth_suap_get_last_sort_order($tablename) {
    global $DB;
    $l = $DB->get_record_sql('SELECT coalesce(max(sortorder), 0) + 1 as sortorder from {' . $tablename . '}');
    return $l->sortorder;
}


function auth_suap_get_or_create($tablename, $keys, $values) {
    global $DB;
    $record = $DB->get_record($tablename, $keys);
    if (!$record) {
        $record = (object)array_merge($keys, $values);
        $record->id = $DB->insert_record($tablename, $record);
    }
    return $record;
}


function auth_suap_create_or_update($tablename, $keys, $inserts, $updates=[], $insert_only=[]) {
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


function auth_suap_create_setting_configtext($settings, $name, $default='') {
    $theme_name = 'auth_suap';
    $settings->add(new admin_setting_configtext("$theme_name/$name", get_string($name, $theme_name), get_string("{$name}_desc", $theme_name), $default));  
}


function auth_suap_create_setting_configtextarea($settings, $name, $default='') {
    $theme_name = 'auth_suap';
    $settings->add(new admin_setting_configtextarea("$theme_name/$name", get_string($name, $theme_name), get_string("{$name}_desc", $theme_name), $default));  
}


function auth_suap_save_user_custom_field($categoryid, $shortname, $name, $datatype = 'text', $visible = 1, $p1 = NULL, $p2 = NULL)
{
    return auth_suap_get_or_create(
        'user_info_field',
        ['shortname' => $shortname],
        ['categoryid' => $categoryid, 'name' => $name, 'description' => $name, 'descriptionformat' => 2, 'datatype' => $datatype, 'visible' => $visible, 'param1' => $p1, 'param2' => $p2]
    );
}


function get_auth_suap_config() {
    return get_config('auth_suap');
}
