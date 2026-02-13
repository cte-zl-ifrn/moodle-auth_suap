# auth_suap

Plugin de autenticacao via OAuth2 do SUAP. Faz o login no Moodle e sincroniza dados do usuario, foto e campos de perfil customizados.

## Requisitos
- Moodle 3.7+ (require 2019_05_20_00)
- PHP 7.2+ com extensão cURL habilitada
- Plugin `auth_oauth2` habilitado (core do Moodle)

## Dependencias opcionais
- **local_suap** (opcional) - Se instalado, aplica preferencias customizadas de usuario conforme `default_user_preferences` no primeiro login

## 1. Configuracao no SUAP
- No SUAP, pesquise por auth e selecione **Aplicacoes OAUTH2**
- No canto superior direito, clique em **Adicionar Aplicacao OAUTH2**

### Preencha os campos
- **Nome:** Escolha um nome descritivo para o seu Moodle
- **Authorization grant type:** Authorization code
- **Redirect URIs:** `http://moodle/auth/suap/authenticate.php http://moodle/admin/oauth2callback.php http://moodle/authenticate.php`
- **Client type:** Public
- **Algorithm:** No OIDC support
- **Ativo:** ✅ Marque este campo

### Chaves
- O **Client ID** e o **Client Secret** serao usados no Moodle
- ⚠️ **Guarde o Client Secret**, pois ele nao podera ser visualizado novamente

Clique em **Salvar mudancas**

## 2. Configuracao no Moodle
1. Ativar plugins de Autenticacao:
   - Acesse **Administracao do site > Plugins > Autenticacao > Gerenciar autenticacao**
   - Habilite SUAP (caso ainda não esteja ativados)
2. Definir URL alternativa para login:
   - Role ate **URL alternativa para login (alternateloginurl)** e preencha com: `http://moodle/auth/suap/login.php`

⚠️ **Atencao:** Ao definir a URL alternativa, **todas** as tentativas de login serao redirecionadas para essa pagina. Certifique-se de que **ha pelo menos um usuario com autenticacao OAuth2 e permissoes de administrador** antes de prosseguir, para evitar ficar "preso do lado de fora" do Moodle.

Role ate o final e clique em **Salvar mudancas**

## 3. Configuracao do SUAP no Moodle
1. Acesse **Administracao do site > Plugins > Autenticacao > SUAP**
2. Preencha os campos:
   - **Client ID:** Client ID gerado no SUAP (você gerou no passo 1)
   - **Client Secret:** Client Secret gerado no SUAP(você gerou no passo 1)
   - **Authorize URL:** URL de autorizacao do SUAP, ex.: https://suap.ifrn.edu.br/o/authorize/
   - **Token URL:** URL de token do SUAP, ex.: https://suap.ifrn.edu.br/o/token/
   - **RH/EU URL:** URL da API `eu` do SUAP, ex.: https://suap.ifrn.edu.br/api/rh/eu/
   - **Logout URL:** URL de logout do SUAP, ex.: https://suap.ifrn.edu.br/comum/logout/
3. Clique em **Salvar mudancas**

## 4. Testando o acesso
Agora, ao clicar no botao de login, voce sera redirecionado para a tela de autenticacao do SUAP.
- Se o usuario ja existir no Moodle, suas informacoes serao atualizadas.
- Caso contrario, um novo usuario sera criado automaticamente.

## Fluxo de autenticacao (resumo)
1. `login.php` redireciona para `authorize_url`.
2. O SUAP retorna `code` para `authenticate.php`.
3. `authenticate.php` troca o `code` por `access_token` em `token_url`.
4. O perfil eh buscado em `rh_eu_url` e os dados sao sincronizados.
5. O usuario eh autenticado no Moodle e redirecionado ao destino.

## Endpoints e utilitarios
| Endpoint | Finalidade |
| --- | --- |
| `/auth/suap/login.php` | Inicia o login SUAP |
| `/auth/suap/authenticate.php` | Callback OAuth2 |
| `/auth/suap/logout.php` | Logout completo (SUAP + Moodle) |
| `/auth/suap/dispatch.php` | Gera token de webservice para apps |
| `/auth/suap/health.php` | Exibe configuracoes ativas (debug) para quem tem a credencial |

## Webservice para apps (dispatch.php)
`dispatch.php` valida o token recebido no header `Authentication: Token <token>` e gera um token de webservice do Moodle.

Para funcionar, configure `verify_token_url` em `auth_suap` (tabela `config_plugins`), pois o plugin nao possui este campo na tela de configuracao.

## Campos de perfil criados automaticamente
Na instalacao/atualizacao o plugin cria o grupo **SUAP** de campos customizados e registra:
- `data_de_nascimento`
- `sexo`
- `id_doc_certificado`
- `tipo_doc_certificado`
- `cpf` (preterido em favor de `id_doc_certificado` e `tipo_doc_certificado`)
- `passaporte` (preterido em favor de `id_doc_certificado` e `tipo_doc_certificado`)

## Campos alterados no primeiro login x nos logins seguintes

Tabela baseada no fluxo de criacao/atualizacao em auth.php.

| Campo | Primeiro login | Logins seguintes | Observacoes |
| --- | --- | --- | --- |
| user.username | Sim (criacao) | Nao | **IFRN-id** vindo do SUAP (atributo `identificacao`) |
| user.password | Sim (criacao) | Nao | Senha aleatoria local (é ignorada). |
| user.timezone | Sim (criacao) | Nao | `99` |
| user.confirmed | Sim (criacao) | Nao | `1` |
| user.mnethostid | Sim (criacao) | Nao | `1` |
| user.policyagreed | Sim (criacao) | Nao | `0` |
| user.deleted | Sim (criacao) | Nao | `0` |
| user.firstaccess | Sim (criacao) | Nao | Timestamp atual. |
| user.currentlogin | Sim (criacao) | Nao | Timestamp atual. |
| user.lastip | Sim (criacao) | Nao | IP remoto. |
| user.firstnamephonetic | Sim (criacao) | Nao | `null`. |
| user.lastnamephonetic | Sim (criacao) | Nao | `null`. |
| user.middlename | Sim (criacao) | Nao | `null`. |
| user.alternatename | Sim (criacao) | Nao | `null`. |
| user.firstname | Sim | Sim | Derivado de `nome_social` ou `nome_registro`, nessa ordem. Exceto a última parte. |
| user.lastname | Sim | Sim | Derivado de `nome_social` ou `nome_registro`, nessa ordem. Apenas a última parte. |
| user.email | Sim | Sim | `email_preferencial` |
| user.auth | Sim | Sim | `suap` |
| user.suspended | Sim | Sim | `0` |
| user.picture | Sim (se foto) | Sim (se foto) | Atualizado via `process_new_icon` do Moodle. |
| profile_field_nome_apresentacao | Sim | Sim | `nome_usual` |
| profile_field_nome_completo | Sim | Sim | `nome_registro` |
| profile_field_nome_social | Sim | Sim | `nome_social` |
| profile_field_email_secundario | Sim | Sim | `email_secundario` |
| profile_field_email_google_classroom | Sim | Sim | `email_google_classroom` |
| profile_field_email_academico | Sim | Sim | `email_academico` |
| profile_field_campus_sigla | Sim | Sim | `campus` |
| profile_field_last_login | Sim | Sim | JSON com o payload do SUAP. Usado para suporte. |
| profile_field_tipo_usuario | Sim | Sim | `tipo_usuario` |
| profile_field_data_de_nascimento | Sim | Sim | `data_de_nascimento` |
| profile_field_sexo | Sim | Sim | `sexo` |
| profile_field_cpf | Sim | Sim | `cpf`. Descontinuado. |
| profile_field_passaporte | Sim | Sim | `passaporte`. Descontinuado. |
| profile_field_id_doc_certificado | Sim (se cpf/passaporte) | Sim (se cpf/passaporte) | `cpf` ou `passaporte`, se não houver CPF. |
| profile_field_tipo_doc_certificado | Sim (se cpf/passaporte) | Sim (se cpf/passaporte) | `CPF` ou `Passaporte`, se não houver CPF. |
| preferencia de usuario (local_suap) | Sim (criacao) | Nao | Conforme `default_user_preferences` configurado no admin do Moodle. |

## Observacoes
- O campo `cpf` e `passaporte` estao marcados como descontinuados, mas ainda podem ser recebidos do SUAP.
- Se a foto estiver disponivel, ela eh salva como `user.picture` via `process_new_icon`.
- `profile_field_last_login` guarda o JSON completo recebido do SUAP para suporte.
