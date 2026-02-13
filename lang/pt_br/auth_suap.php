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

$string['pluginname'] = 'Autenticação OAuth2 SUAP';
$string['auth_suap_description'] = 'SUAP é o Sistema Unificado de Administração Pública utilizado em instituições federais brasileiras, incluindo o Instituto Federal do Rio Grande do Norte (IFRN). Este plugin possibilita integração de Single Sign-On (SSO), permitindo que alunos e servidores façam login no Moodle usando suas credenciais do SUAP. Sincroniza automaticamente dados do usuário (nome, email, CPF, status de matrícula) e suporta controle de acesso baseado em papéis conforme dados institucionais.';
$string['auth_description'] = 'Autenticação OAuth2';

$string['client_id'] = 'ID do Cliente OAuth2';
$string['client_id_desc'] = "Obtenha no SUAP: Gestão de Tecnologia > Serviços > Aplicações OAuth2. Registre sua instância do Moodle com tipo de autorização 'Código de autorização' (cliente público) e defina o URI de redirecionamento para: {$CFG->wwwroot}/auth/suap/authenticate.php";

$string['client_secret'] = 'Segredo do Cliente OAuth2';
$string['client_secret_desc'] = "Este segredo é exibido apenas uma vez quando você cria a aplicação OAuth2 no SUAP. Salve imediatamente pois não pode ser recuperado depois. Para gerar um novo segredo, registre uma nova aplicação no SUAP.";

$string['authorize_url'] = "Ponto de Acesso de Autorização OAuth2 do SUAP";
$string['authorize_url_desc'] = "URL do ponto de acesso de autorização OAuth2 do SUAP (tipicamente https://suap.ifrn.edu.br/o/authorize/)";

$string['token_url'] = "Ponto de Acesso de Token OAuth2 do SUAP";
$string['token_url_desc'] = "URL do ponto de acesso de troca de token OAuth2 do SUAP (tipicamente https://suap.ifrn.edu.br/o/token/)";

$string['rh_eu_url'] = "Ponto de Acesso da API RH/EU do SUAP";
$string['rh_eu_url_desc'] = "Ponto de acesso da API do SUAP para recuperar dados de identificação e documentos pessoais do usuário (tipicamente https://suap.ifrn.edu.br/api/eu/)";

$string['logout_url'] = "URL de Logout do SUAP";
$string['logout_url_desc'] = "Ponto de acesso de logout do SUAP para encerrar a sessão (tipicamente https://suap.ifrn.edu.br/o/logout/)";
