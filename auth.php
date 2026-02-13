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
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/user/lib.php");
require_once("$CFG->dirroot/user/profile/lib.php");
require_once("$CFG->dirroot/lib/authlib.php");
require_once("$CFG->dirroot/lib/classes/user.php");
require_once("$CFG->dirroot/auth/suap/locallib.php");


class auth_plugin_suap extends auth_oauth2\auth
{
    public $authtype;
    public $roleauth;
    public $errorlogtag;
    public $config;
    public $usuario;

    public function __construct()
    {
        $this->authtype = 'suap';
        $this->roleauth = 'auth_suap';
        $this->errorlogtag = '[AUTH SUAP] ';
        $this->config = get_auth_suap_config();
        $this->usuario = null;
    }

    public function user_login($username, $password)
    {
        return false;
    }

    public function can_change_password()
    {
        return false;
    }

    public function is_internal()
    {
        return false;
    }

    function postlogout_hook($user)
    {
        global $CFG;
        if ($user->auth != 'suap') {
            return 0;
        }
        $config = get_auth_suap_config();
        redirect($CFG->wwwroot . '/auth/suap/logout.php');
    }

    public function login()
    {
        global $CFG, $USER, $SESSION;

        $next = optional_param('next', '', PARAM_LOCALURL);
        if (empty($next)) {
            if (property_exists($SESSION, 'wantsurl')) {
                $next = $SESSION->wantsurl;
            } else {
                $next = $CFG->wwwroot;
            }
        }

        if ($USER->id) {
            header("Location: $next", true, 302);
        } else {
            $SESSION->next_after_next = $next;
            $redirect_uri = "$CFG->wwwroot/auth/suap/authenticate.php";
            header("Location: {$this->config->authorize_url}?response_type=code&client_id={$this->config->client_id}&redirect_uri=$redirect_uri", true, 302);
        }
    }

    public function authenticate()
    {
        global $CFG, $USER;

        if ($USER->id) {
            header("Location: /", true, 302);
            die();
        }

        $code = required_param('code', PARAM_RAW);

        $token_response = "";
        $user_data_response = "";
        try {
            // Exchange code for access token
            $token_response = auth_suap_curl_post(
                $this->config->token_url,
                [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => "{$CFG->wwwroot}/auth/suap/authenticate.php",
                    'client_id' => $this->config->client_id,
                    'client_secret' => $this->config->client_secret
                ]
            );
            $auth = json_decode($token_response);

            // Get user data from SUAP
            $user_data_response = auth_suap_curl_get(
                "{$this->config->rh_eu_url}?scope=" . urlencode('identificacao documentos_pessoais'),
                [
                    "Authorization: Bearer {$auth->access_token}",
                    "x-api-key: {$this->config->client_secret}",
                    "Accept: application/json"
                ]
            );
            if (strpos($user_data_response, '"identificacao"') === false) {
                throw new Exception("Erro ao tentar obter dados do SUAP.");
            }

            $userdata = json_decode($user_data_response);
            $this->create_or_update_user($userdata);
        } catch (Exception $e) {
            // Log error for administrators
            error_log('[AUTH SUAP] OAuth2 Authentication Error: ' . $e->getMessage());
            
            // Display user-friendly error message
            print_error('auth_failure', 'auth_suap', '', null, $e->getMessage());
            die();
        }
    }

    function create_or_update_user($userdata)
    {
        global $DB, $SESSION, $CFG;

        if (!property_exists($userdata, 'identificacao')) {
            echo "<p>Erro ao integrar com o SUAP.</p>";
            echo "<pre style='display: None'>";
            var_dump($userdata);
            echo "</pre>";
            die();
        }
        $usuario = $DB->get_record("user", ["username" => strtolower($userdata->identificacao)]);

        if ($userdata->nome_social) {
            if (count(explode(' ', $userdata->nome_social)) == 1) {
                $parts = explode(' ', $userdata->nome_registro);
                $userdata->primeiro_nome = $userdata->nome_social . ' ' . implode(' ', array_slice($parts, 1, -1));
                $userdata->ultimo_nome = array_slice($parts, -1)[0];
            } else {
                $userdata->primeiro_nome = implode(' ', array_slice(explode(' ', $userdata->nome_social), 0, -1));
                $userdata->ultimo_nome = array_slice(explode(' ', $userdata->nome_social), -1)[0];
            }
        }
        if (empty($userdata->nome_social)) {
            $parts = explode(' ', $userdata->nome_registro);
            $userdata->primeiro_nome = implode(' ', array_slice($parts, 0, -1));
            $userdata->ultimo_nome = end($parts);
        }

        if (!$usuario) {
            $usuario = (object)[
                'username' => strtolower($userdata->identificacao),
                'firstname' => $userdata->primeiro_nome,
                'lastname' => $userdata->ultimo_nome,
                'email' => $userdata->email_preferencial,
                'auth' => 'suap',
                'suspended' => 0,
                'password' => '!aA1' . uniqid(),
                'timezone' => '99',
                // 'lang'=>'pt_br',
                'confirmed' => 1,
                'mnethostid' => 1,
                'policyagreed' => 0,
                'deleted' => 0,
                'firstaccess' => time(),
                'currentlogin' => time(),
                'lastip' => getremoteaddr(),
                'firstnamephonetic' => null,
                'lastnamephonetic' => null,
                'middlename' => null,
                'alternatename' => null,
            ];
            $usuario->id = \user_create_user($usuario);

            $default_user_preferences = get_config('local/suap', 'default_user_preferences');
            foreach (preg_split('/\r\n|\r|\n/', $default_user_preferences) as $preference) {
                $parts = explode("=", $preference);
                if (count($parts) == 2) {
                    \set_user_preference($parts[0], $parts[1], $usuario);
                }
            }
        }

        $parts = explode(' ', $userdata->primeiro_nome);
        $usuario->firstname = $userdata->primeiro_nome;
        $usuario->lastname = $userdata->ultimo_nome ?: end($parts);
        $usuario->email = $userdata->email_preferencial;
        $usuario->auth = 'suap';
        $usuario->suspended = 0;
        $usuario->profile_field_nome_apresentacao = $userdata->nome_usual;
        $usuario->profile_field_nome_completo = property_exists($userdata, 'nome_registro') ? $userdata->nome_registro : null;
        $usuario->profile_field_nome_social = property_exists($userdata, 'nome_social') ? $userdata->nome_social : null;
        $usuario->profile_field_email_secundario = property_exists($userdata, 'email_secundario') ? $userdata->email_secundario : null;
        $usuario->profile_field_email_google_classroom = property_exists($userdata, 'email_google_classroom') ? $userdata->email_google_classroom : null;
        $usuario->profile_field_email_academico = property_exists($userdata, 'email_academico') ? $userdata->email_academico : null;
        $usuario->profile_field_campus_sigla = property_exists($userdata, 'campus') ? $userdata->campus : null;
        $usuario->profile_field_last_login = \json_encode($userdata);
        $usuario->profile_field_tipo_usuario = property_exists($userdata, 'tipo_usuario') ? $userdata->tipo_usuario : null;

        $usuario->profile_field_data_de_nascimento = property_exists($userdata, 'data_de_nascimento') ? $userdata->data_de_nascimento : null;
        $usuario->profile_field_sexo = property_exists($userdata, 'sexo') ? $userdata->sexo : null;
        $usuario->profile_field_cpf = property_exists($userdata, 'cpf') ? $userdata->cpf : null;
        $usuario->profile_field_passaporte = property_exists($userdata, 'passaporte') ? $userdata->passaporte : null;

        if ($usuario->profile_field_cpf || $usuario->profile_field_passaporte) {
            $usuario->profile_field_id_doc_certificado = $usuario->profile_field_cpf ? $usuario->profile_field_cpf : $usuario->profile_field_passaporte;
            $usuario->profile_field_tipo_doc_certificado = $usuario->profile_field_cpf ? "CPF" : "Passaporte";
        }

        $this->usuario = $usuario;
        $next = $SESSION->next_after_next;

        $this->update_user_record($this->usuario->username);
        if (property_exists($userdata, 'foto') && $userdata->foto) {
            $this->update_picture($usuario, $userdata->foto);
        }
        $usuario = $DB->get_record("user", ["username" => strtolower($userdata->identificacao)]);

        complete_user_login($usuario);

        header("Location: $next", true, 302);
    }

    function update_picture($usuario, $foto)
    {
        global $CFG, $DB;
        require_once($CFG->libdir . '/gdlib.php');

        $conf = get_auth_suap_config();

        $tmp_filename = $CFG->tempdir . '/suapfoto' . $usuario->id;
        file_put_contents($tmp_filename, file_get_contents($foto));
        $usuario->imagefile = process_new_icon(context_user::instance($usuario->id, MUST_EXIST), 'user', 'icon', 0, $tmp_filename);
        if ($usuario->imagefile) {
            $DB->set_field('user', 'picture', $usuario->imagefile, ['id' => $usuario->id]);
        }
    }

    function get_userinfo($username)
    {
        return get_object_vars($this->usuario);
    }
}
