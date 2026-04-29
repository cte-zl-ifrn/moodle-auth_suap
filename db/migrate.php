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

    $suap = auth_suap_get_or_create('user_info_category', ['name' => 'SUAP'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($suap, 'tipo_usuario', 'Tipo de usuário');
    auth_suap_save_user_custom_field($suap, 'eh_servidor', 'É servidor', 'checkbox');
    auth_suap_save_user_custom_field($suap, 'eh_aluno', 'É aluno', 'checkbox');
    auth_suap_save_user_custom_field($suap, 'eh_prestador', 'É prestador', 'checkbox');
    auth_suap_save_user_custom_field($suap, 'eh_usuarioexterno', 'É usuário externo', 'checkbox');
    auth_suap_save_user_custom_field($suap, 'eh_docente', 'É docente', 'checkbox');
    auth_suap_save_user_custom_field($suap, 'eh_tecnico_administrativo', 'É técnico administrativo', 'checkbox');
    auth_suap_save_user_custom_field($suap, 'last_login', 'JSON do último login', 'textarea', 0);

    $pessoais = auth_suap_get_or_create('user_info_category', ['name' => 'Dados pessoais'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($pessoais, 'nome_apresentacao', 'Nome de apresentação');
    auth_suap_save_user_custom_field($pessoais, 'nome_completo', 'Nome completo');
    auth_suap_save_user_custom_field($pessoais, 'nome_social', 'Nome social');
    auth_suap_save_user_custom_field($pessoais, 'data_de_nascimento', 'Data de nascimento');
    auth_suap_save_user_custom_field($pessoais, 'sexo', 'Sexo');
    auth_suap_save_user_custom_field($pessoais, 'cpf', 'CPF');
    auth_suap_save_user_custom_field($pessoais, 'passaporte', 'Passaporte');
    auth_suap_save_user_custom_field($pessoais, 'id_doc_certificado', 'ID do documento para certificado');
    auth_suap_save_user_custom_field($pessoais, 'tipo_doc_certificado', 'Tipo de documento para certificado');
    auth_suap_save_user_custom_field($pessoais, 'eh_estrangeiro', 'É estrangeiro', 'checkbox');

    $contatos = auth_suap_get_or_create('user_info_category', ['name' => 'Dados de contato'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($contatos, 'email_google_classroom', 'E-mail @escolar (Google Classroom)');
    auth_suap_save_user_custom_field($contatos, 'email_academico', 'E-mail @academico (Microsoft)');
    auth_suap_save_user_custom_field($contatos, 'email_secundario', 'Secundário (servidores)');

    $matricula = auth_suap_get_or_create('user_info_category', ['name' => 'Matrícula'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($matricula, 'programa_nome', 'Nome do programa');
    auth_suap_save_user_custom_field($matricula, 'ingresso_periodo', 'Período de ingresso');
    auth_suap_save_user_custom_field($matricula, 'outras_matriculas', 'Outras matrículas');    

    $polo = auth_suap_get_or_create('user_info_category', ['name' => 'Polo'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($polo, 'polo_id', 'ID do polo');
    auth_suap_save_user_custom_field($polo, 'polo_nome', 'Nome do polo');
    auth_suap_save_user_custom_field($polo, 'polo_sigla', 'Sigla do polo');

    $campus = auth_suap_get_or_create('user_info_category', ['name' => 'Campus'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($campus, 'campus_id', 'ID do campus');
    auth_suap_save_user_custom_field($campus, 'campus_descricao', 'Descrição do campus');
    auth_suap_save_user_custom_field($campus, 'campus_sigla', 'Sigla do campus');

    $curso = auth_suap_get_or_create('user_info_category', ['name' => 'Curso'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($curso, 'curso_id', 'ID do curso');
    auth_suap_save_user_custom_field($curso, 'curso_codigo', 'Código do curso');
    auth_suap_save_user_custom_field($curso, 'curso_descricao', 'Descrição do curso');
    auth_suap_save_user_custom_field($curso, 'curso_modalidade_id', 'Id da modalidade');
    auth_suap_save_user_custom_field($curso, 'curso_modalidade_descricao', 'Descrição da modalidade');
    auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino_id', 'Id do nível de ensino');
    auth_suap_save_user_custom_field($curso, 'curso_nivel_ensino_descricao', 'Descrição do nível de ensino');

    $turma = auth_suap_get_or_create('user_info_category', ['name' => 'Turma'], ['sortorder' => auth_suap_get_last_sort_order('user_info_category')])->id;
    auth_suap_save_user_custom_field($turma, 'turma_id', 'ID da última turma');
    auth_suap_save_user_custom_field($turma, 'turma_codigo', 'Código última da turma');

    return true;
}
