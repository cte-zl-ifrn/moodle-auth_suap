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

require_once('../../config.php');
require_once("$CFG->dirroot/auth/suap/locallib.php");

$config = get_auth_suap_config();
\core\session\manager::init_empty_session();
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <p style='text-align: center; margin-top: 2rem;'>Para sair completamente é necessário que você confirmar no botão
        abaixo.
        <span style='text-align: center; font-size: 90%; padding: 0 2rem; font-style: italic; display: block;'>Assim
            você sairá do SUAP e será reencaminhado para a página de acesso ao SUAP.</span>
    </p>
    <p style='text-align: center;'><a href="<?php echo $config->logout_url ?>" class='btn btn-primary'>Confirmar saída</a></p>
    <p style='text-align: center; margin-top: 2rem;'>Ou você pode <a href="<?php echo $CFG->wwwroot; ?>">continuar
            conectado</a>.
    </p>
</body>

</html>