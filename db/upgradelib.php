<?php
/**
 * Plugin upgrade helper functions are defined here.
 *
 * @package     auth_suap
 * @category    upgrade
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/auth/suap/locallib.php');


function auth_suap_bulk_user_custom_field()
{
    global $DB;

    $cid = \auth_suap\get_or_create('user_info_category', ['name' => 'SUAP'], ['sortorder' => \auth_suap\get_last_sort_order('user_info_category')])->id;

    \auth_suap\save_user_custom_field($cid, 'data_de_nascimento', 'Data de nascimento');
    \auth_suap\save_user_custom_field($cid, 'sexo', 'Sexo');
    \auth_suap\save_user_custom_field($cid, 'cpf', 'CPF');
    \auth_suap\save_user_custom_field($cid, 'passaporte', 'Passaporte');
    \auth_suap\save_user_custom_field($cid, 'id_doc_certificado', 'ID do documento para certificado');
    \auth_suap\save_user_custom_field($cid, 'tipo_doc_certificado', 'Tipo de documento para certificado');
}
