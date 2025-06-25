<?php

/**
 * Authentication class for suap is defined here.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/user/lib.php");
require_once("$CFG->dirroot/user/profile/lib.php");
require_once("$CFG->dirroot/lib/authlib.php");
require_once("$CFG->dirroot/lib/classes/user.php");
require_once("$CFG->dirroot/auth/suap/classes/Httpful/Bootstrap.php");
\Httpful\Bootstrap::init();


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
        $this->config = get_config('auth_suap');
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
        $config = get_config('auth/suap');
        redirect($CFG->wwwroot . '/auth/suap/logout.php');
    }

    public function login()
    {
        global $CFG, $USER, $SESSION;

        if (isset($_GET['next'])) {
            $next = $_GET['next'];
        } elseif (property_exists($SESSION, 'wantsurl')) {
            $next = $SESSION->wantsurl;
        } else {
            $next = $CFG->wwwroot;
        }

        if ($USER->id) {
            header("Location: $next", true, 302);
        } else {
            $SESSION->next_after_next = $next;
            $redirect_uri = "$CFG->wwwroot/auth/suap/authenticate.php";
            header("Location: {$this->config->base_url}/o/authorize/?response_type=code&client_id={$this->config->client_id}&redirect_uri=$redirect_uri", true, 302);
        }
    }

    public function authenticate()
    {
        global $CFG, $USER;

        if ($USER->id) {
            header("Location: /", true, 302);
            die();
        }

        $conf = get_config('auth_suap');

        if (!isset($_GET['code'])) {
            throw new Exception("O código de autenticação não foi informado.");
        }

        $user_data_response = "";
        try {
            $auth = json_decode(
                \Httpful\Request::post(
                    "$conf->base_url/o/token/",
                    [
                        'grant_type' => 'authorization_code',
                        'code' => $_GET['code'],
                        'redirect_uri' => "{$CFG->wwwroot}/auth/suap/authenticate.php",
                        'client_id' => $conf->client_id,
                        'client_secret' => $conf->client_secret
                    ],
                    \Httpful\Mime::FORM
                )->send()->raw_body
            );

            try {
                // Tenta o SUAP Monolítico
                $$user_data_response = \Httpful\Request::get("$conf->base_url/api/eu/?scope=" . urlencode('identificacao documentos_pessoais'))
                    ->addHeaders(["Authorization" => "Bearer {$auth->access_token}", 'x-api-key' => $conf->client_secret, 'Accept' => 'application/json'])
                    ->send()->raw_body;
                if (strpos($$user_data_response, '"identificacao"') === false) {
                    throw new Exception("Erro ao tentar obter dados do SUAP Monolítico.");
                }
            } catch (Exception $e) {
                // Tenta o SUAP Login
                $$user_data_response = \Httpful\Request::get("$conf->base_url/api/v1/userinfo/?scope=" . urlencode('read'))
                    ->addHeaders(["Authorization" => "Bearer {$auth->access_token}", 'x-api-key' => $conf->client_secret, 'Accept' => 'application/json'])
                    ->send()->raw_body;
                if (strpos($$user_data_response, '"identificacao"') === false) {
                    throw new Exception("Erro ao tentar obter dados do SUAP Login.");
                }
            }

            $userdata = json_decode($$user_data_response);
            $this->create_or_update_user($userdata);
        } catch (Exception $e) {
?>
            <!DOCTYPE html>
            <html lang="pt-BR" dir="ltr">

            <head>
                <meta charset="utf-8">
                <meta http-equiv="x-ua-compatible" content="ie=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="description" content="SUAP: Em Manutenção">
                <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
                <meta http-equiv="Pragma" content="no-cache" />
                <meta http-equiv="Expires" content="0" />
                <title>SUAP: Em Manutenção</title>
                <link rel="shortcut icon" href="https://suap.ifrn.edu.br/static/comum/img/favicon-maintenance.png" />
                <style>
                    :root {
                        --grey-200: #eee;
                        --grey-500: #999;
                        --warning: #c29d0b;
                    }

                    * {
                        border: 0 none;
                        margin: 0;
                        outline: 0 none;
                        padding: 0;
                    }

                    *,
                    *:after,
                    *:before {
                        -webkit-box-sizing: border-box;
                        -moz-box-sizing: border-box;
                        box-sizing: border-box;
                    }

                    html {
                        height: 100%;
                    }

                    body {
                        font-family: sans-serif;
                        font-size: 100%;
                        height: 100%;
                    }

                    header {
                        border-bottom: 1px solid var(--grey-200);
                        display: flex;
                        align-items: center;
                        gap: 2rem;
                        justify-content: space-between;
                        padding: 2rem 10rem;
                        width: 100%;
                    }

                    h1 a {
                        color: var(--grey-500);
                        display: block;
                        font-size: 1.25rem;
                        font-weight: 300;
                        line-height: 1.25em;
                        text-decoration: none;
                    }

                    main {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 5rem;
                        justify-content: center;
                        padding: 10rem;
                    }

                    main svg {
                        max-width: 200px;
                        opacity: .15;
                    }

                    section {
                        flex-basis: 50%;
                    }

                    h2 {
                        color: var(--warning);
                        font-size: 3rem;
                        font-weight: 900;
                        line-height: 1em;
                        padding-bottom: 1rem;
                    }

                    p {
                        font-size: 1.5rem;
                        font-weight: 300;
                        line-height: 1.5em;
                    }

                    .obs {
                        color: var(--grey-500);
                        font-size: .9rem;
                        font-weight: 300;
                        padding-top: 2rem;
                    }

                    @media screen and (min-width: 2156px) {
                        section {
                            flex-basis: 30%;
                        }
                    }

                    @media screen and (max-width: 1000px) {

                        header,
                        main {
                            padding-left: 2rem;
                            padding-right: 2rem;
                        }
                    }

                    @media screen and (max-width: 900px) {
                        main {
                            padding-top: 5rem;
                            padding-bottom: 5rem;
                        }

                        main svg {
                            display: none;
                        }

                        section {
                            flex-basis: 100%;
                        }
                    }

                    @media screen and (max-width: 650px) {
                        h1 a {
                            font-size: 1rem;
                            text-align: right;
                        }
                    }

                    @media screen and (max-width: 940px),
                    only screen and (min-device-width: 320px) and (max-device-width: 736px) and (-webkit-min-device-pixel-ratio: 2),
                    only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 1),
                    only screen and (min-device-width: 768px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) {
                        main {
                            min-height: calc(100vh - 120px);
                            padding-bottom: 2rem;
                        }

                        main section {
                            display: flex;
                            flex-direction: column;
                        }

                        h2 {
                            font-size: 2.5rem;
                        }

                        .obs {
                            margin-top: auto;
                        }
                    }
                </style>
            </head>

            <body>

                <header>
                    <svg width="100" height="50" viewBox="0 0 100 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M0.732763 8.00293C0.732763 11.2153 3.04333 13.9206 8.00294 15.7239C11.61 17.0764 13.0188 18.3172 13.0188 20.6835C13.0188 22.9945 11.3283 24.7413 7.77768 24.7413C5.29763 24.7413 2.70531 23.7273 1.18377 22.8258L0 26.5449C1.91629 27.6721 4.67762 28.4612 7.66493 28.4612C14.0336 28.4612 17.8094 25.0801 17.8094 20.2327C17.8094 16.1749 15.3296 13.8083 10.652 12.0605C7.10117 10.652 5.52337 9.63747 5.52337 7.38316C5.52337 5.35388 7.10117 3.66333 10.032 3.66333C12.568 3.66333 14.5408 4.62136 15.6114 5.24185L16.7951 1.69079C15.3296 0.733 12.9063 0 10.1445 0C4.33984 0 0.732763 3.60707 0.732763 8.00293Z"
                            fill="#7A7F80" />
                        <path
                            d="M39.2254 0.620241V17.3024C39.2254 18.2604 39.0562 19.1622 38.7744 19.8382C37.8732 22.0928 35.5621 24.4033 32.2368 24.4033C27.7844 24.4033 26.1504 20.8527 26.1504 15.6674V0.620241H21.191V16.5694C21.191 26.0941 26.3194 28.5179 30.6025 28.5179C35.4494 28.5179 38.3802 25.6429 39.62 23.4453H39.7327L40.0142 27.8977H44.4101C44.2413 25.7561 44.1848 23.2765 44.1848 20.402V0.620241H39.2254Z"
                            fill="#7A7F80" />
                        <path
                            d="M49.3699 2.36729L50.4972 5.69238C52.413 4.39586 55.0618 3.66333 57.6546 3.66333C63.2338 3.60708 63.8543 7.72117 63.8543 9.97548V10.5395C53.3148 10.483 47.4536 14.0898 47.4536 20.6835C47.4536 24.6293 50.2717 28.5179 55.7948 28.5179C59.6832 28.5179 62.6707 26.6011 64.1358 24.4598H64.3048L64.7558 27.8977H69.2082C68.9264 26.0376 68.8137 23.7273 68.8137 21.3602V11.159C68.8137 5.69238 66.7844 2.24444e-06 58.3871 2.24444e-06C54.9496 2.24444e-06 51.6242 0.958023 49.3699 2.36729ZM52.413 20.1207C52.413 14.8223 58.5564 13.8641 63.9668 13.9768V18.7114C63.9668 19.2187 63.9103 19.782 63.741 20.289C62.9525 22.6 60.6417 24.8541 57.0346 24.8541C54.5553 24.8541 52.413 23.3888 52.413 20.1207Z"
                            fill="#7A7F80" />
                        <path
                            d="M78.5636 5.29787H78.4506L78.1689 0.620248H73.7168C73.8855 3.21233 73.9418 6.03039 73.9418 9.52472V50H78.9014V24.2341H79.0144C80.6485 26.9394 83.7483 28.5179 87.4114 28.5179C93.78 28.5179 99.8106 23.671 99.8106 13.8641C99.8106 5.57987 94.8513 1.04181e-05 88.2013 1.04181e-05C83.8048 1.04181e-05 80.5362 1.9163 78.5636 5.29787ZM79.1274 18.8237C78.9579 18.2039 78.9014 17.4709 78.9014 16.7951V12.0606C78.9014 11.4413 79.0707 10.708 79.1834 10.0885C80.1412 6.31263 83.354 3.9451 86.7353 3.9451C91.977 3.9451 94.7948 8.62296 94.7948 14.0898C94.7948 20.3457 91.7514 24.6293 86.5098 24.6293C83.0157 24.6293 79.9724 22.262 79.1274 18.8237Z"
                            fill="#7A7F80" />
                        <path d="M25.5956 49.9944V35.6805H28.6307V49.9944H25.5956Z" fill="#2F9E41" />
                        <path d="M35.2198 49.9944H32.2337V35.6805H40.4382V38.1673H35.2198V41.8584H40.076V44.3354H35.2198V49.9944Z"
                            fill="#2F9E41" />
                        <path
                            d="M47.4092 35.6805C48.7081 35.6805 49.7785 35.8372 50.6205 36.1505C51.469 36.4638 52.0989 36.937 52.5101 37.5701C52.9213 38.2032 53.1269 39.0028 53.1269 39.9688C53.1269 40.6215 53.0029 41.1926 52.7549 41.6822C52.5068 42.1717 52.1805 42.5862 51.7758 42.9256C51.3711 43.265 50.9338 43.5424 50.4639 43.7578L54.6738 49.9944H51.3059L47.8889 44.5019H46.2735V49.9944H43.2384V35.6805H47.4092ZM47.1938 38.1673H46.2735V42.0346H47.2525C48.2577 42.0346 48.9757 41.8682 49.4065 41.5353C49.8438 41.1959 50.0625 40.6998 50.0625 40.0471C50.0625 39.3683 49.8275 38.8853 49.3575 38.5981C48.8941 38.3109 48.1729 38.1673 47.1938 38.1673Z"
                            fill="#2F9E41" />
                        <path
                            d="M69.1738 49.9944H65.3163L59.0894 39.166H59.0013C59.0274 39.6164 59.0503 40.07 59.0698 40.5269C59.0894 40.9838 59.109 41.4407 59.1286 41.8976C59.1482 42.3479 59.1678 42.8016 59.1873 43.2585V49.9944H56.4753V35.6805H60.3035L66.5205 46.4013H66.5891C66.576 45.9574 66.5597 45.5168 66.5401 45.0795C66.5205 44.6422 66.5009 44.2049 66.4814 43.7676C66.4683 43.3303 66.4553 42.893 66.4422 42.4556V35.6805H69.1738V49.9944Z"
                            fill="#2F9E41" />
                    </svg>
                    <h1><a href="https://suap.ifrn.edu.br">Sistema Unificado de Administração Pública</a></h1>
                </header>

                <main>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <!--! Font Awesome Free 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. -->
                        <path
                            d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z" />
                    </svg>
                    <section>
                        <h2>Integração com SUAP</h2>
                        <p>Por favor, aguarde. Falha na comunicação com o SUAP.</p>
                        <p class="obs">
                            Se esta mensagem estiver ocorrendo em somente algumas páginas do SUAP, você pode estar com
                            problema de cache no seu navegador. Resolva forçando a atualização da página pressionando as teclas CTRL
                            + F5 ou CTRL + R (no Windows ou Linux), ou CMD + R (no Mac).
                        </p>
                        <p class="obs">
                            Se o erro persistir, por favor, procure a TI da reitoria e fale com o suporte do SUAP.
                        </p>
                    </section>
                </main>

            </body>

            </html>
<?php
            die();
        }
        echo "<p>Erro ao tentar integrar com o SUAP. Não foi possível obter seus dados da API.</a>.";
        die();
    }

    function create_or_update_user($userdata)
    {
        /*
            {
                "identificacao": "123456789",
                "nome_social": "",
                "nome_usual": "Nome Outros",
                "nome_registro": "Nome Outros Nomes Sobrenome",
                "nome": "Nome Sobrenome",
                "primeiro_nome": "Nome",
                "ultimo_nome": "Sobrenome",
                "email": "nome.sobrenome@ifrn.edu.br",
                "email_secundario": "nome.sobrenome@gmail.com",
                "email_google_classroom": "nome.sobrenome@escolar.ifrn.edu.br",
                "email_academico": "nome.sobrenome@academico.ifrn.edu.br",
                "campus": "RE",
                "foto":"https://cdn.suap.ifrn.edu.br/media/fotos/75x100/159574.4t54kAqLqyPB.jpg?X-Amz-Algorithm=...&X-Amz-Credential=...&X-Amz-Date=...&X-Amz-Expires=...&X-Amz-SignedHeaders=...&X-Amz-Signature=...",
                "tipo_usuario": "Servidor (Técnico-Administrativo)",
                "email_preferencial": "nome.sobrenome@ifrn.edu.br"
            }

            // Antes a foto era relativa ao baseurl do SUAP, agora é absoluta e temporária
        */
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
                'lastip' => $_SERVER['REMOTE_ADDR'],
                'firstnamephonetic' => null,
                'lastnamephonetic' => null,
                'middlename' => null,
                'alternatename' => null,
            ];
            $usuario->id = \user_create_user($usuario);
        }

        $usuario->firstname = $userdata->primeiro_nome;
        $usuario->lastname = $userdata->ultimo_nome;
        $usuario->email = $userdata->email_preferencial;
        $usuario->auth = 'suap';
        $usuario->suspended = 0;
        $usuario->profile_field_nome_apresentacao = $userdata->nome_usuaal;
        $usuario->profile_field_nome_completo = property_exists($userdata, 'nome_registro') ? $userdata->nome_registro : null;
        $usuario->profile_field_nome_social = property_exists($userdata, 'nome_social') ? $userdata->nome_social : null;
        $usuario->profile_field_email_secundario = property_exists($userdata, 'email_secundario') ? $userdata->email_secundario : null;
        $usuario->profile_field_email_google_classroom = property_exists($userdata, 'email_google_classroom') ? $userdata->email_google_classroom : null;
        $usuario->profile_field_email_academico = property_exists($userdata, 'email_academico') ? $userdata->email_academico : null;
        $usuario->profile_field_campus_sigla = property_exists($userdata, 'campus') ? $userdata->campus : null;
        $usuario->profile_field_last_login = \json_encode($userdata);
        $usuario->profile_field_tipo_usuario = property_exists($userdata, 'tipo_usuario') ? $userdata->tipo_usuario : null;
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

        $conf = get_config('auth_suap');

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
