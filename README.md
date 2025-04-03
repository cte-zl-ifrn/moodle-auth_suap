# moodle__auth_suap
auth_suap
## Configuração da autenticação via SUAP no Moodle Local
⚠️ **Atenção:** Se o seu Moodle local estiver configurado com o endereço `http://moodle/`, então eu já criei um **Client ID** e **Client Secret**, então pode pular a etapa de configuração do SUAP. 
- **Client ID:** `rBsfSfse87W9bjjYglVjIVjsxfHQyAWKM2oq16oW`
- **Client Secret:** `4kk4FfnRpIMKrIVoIeW2WBRWaX9aXhtURkWZr6maE5iHYBORsaS2YYw7aKWHgXugcxbTTUMuvm3NRlgDTtv6kVQr4yN9hwhXFq8vzpoz1pTO2TENvFOQNrDMW75zdst3` 
### 1. Configuração no SUAP
- No SUAP, pesquise por auth e selecione **Aplicações OAUTH2**
- No canto superior direito, clique em **Adicionar Aplicação OAUTH2**
#### Preencha os campos
- **Nome:** Escolha um nome descritivo para o seu Moodle
- **Authorization grant type:** Authorization code
- Redirect URIs:** `http://moodle/auth/suap/authenticate.php http://moodle/admin/oauth2callback.php http://moodle/authenticate.php`
- **Client type:** Public
- **Algorithm:** No OICD support
- **Ativo:** ✅ Marque este campo
#### Chaves
- O **Client ID** e o **Client Secret** serão usados no Moodle
- ⚠️ **Guarde o Client Secret**, pois ele não poderá ser visualizado novamente ⚠️

Clique em **Salvar mudanças**

## 2. Configuração no Moodle
1. Ativar plugins de Autenticação:
    - Acesse **Administração do site > Plugins > Autenticação > Gerenciar autenticação**
    - Habilite OAUTH 2 e SUAP (caso já não estejam ativados)
2. Definir URL Alternativa para login:
    - Role até URL alternativa para login (alternateloginurl) e preencha com: `http://moodle/auth/suap/login.php`

Role até o final e clique em **Salvar mudanças**
## 3. Configuração do SUAP no Moodle
1. Acesse **Administração do site > Plugins > Autenticação > SUAP**.
2. Preencha os campos:
    - **URL base:** `https://suap.ifrn.edu.br`
    - **ID do Cliente:** Client ID gerado no SUAP
    - **Secredo do Cliente:** Client Secret gerado no SUAP
    - **Verify Token URL:** `http://painel/api/v1/verify/`
3. Role até o final e clique em **Salvar mudanças**.
