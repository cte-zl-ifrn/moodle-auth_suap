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

    $cid = get_or_create('user_info_category', ['name' => 'SUAP'], ['sortorder' => get_last_sort_order('user_info_category')])->id;

    save_user_custom_field($cid, 'data_de_nascimento', 'Data de nascimento');
    save_user_custom_field($cid, 'sexo', 'Sexo');
    save_user_custom_field($cid, 'cpf', 'CPF');
    save_user_custom_field($cid, 'passaporte', 'Passaporte');
    save_user_custom_field($cid, 'id_doc_certificado', 'ID do documento para certificado');
    save_user_custom_field($cid, 'tipo_doc_certificado', 'Tipo de documento para certificado');
    return true;
}
