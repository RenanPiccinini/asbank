# ASBANK - Confiança que cresce 🏦⚡

Sistema completo de gestão de empréstimos mútuos e correspondente bancário digital com integração nativa ao **Gateway MisticPay (PIX Cash-In e Cash-Out)**, construído com **Laravel**, **Laravel Breeze** e **MySQL**.

![ASBANK Logo](public/images/logo.jpg)

---

## 🚀 Funcionalidades Principais

### 1. Painel de Gerenciamento do Administrador (`/admin`)
* **Menu Lateral na Esquerda:**
  * **Dashboard:** Visão consolidada com métricas de valor total emprestado, valores recebidos via PIX, parcelas futuras, clientes ativos, fluxo de caixa e status do gateway.
  * **Clientes:**
    * Cadastro completo (Nome, Documento CPF/CNPJ, E-mail, Telefone, Endereço completo, Dados Bancários e Chave PIX).
    * Criação automática de credencial para acesso ao **Portal do Cliente**.
    * Edição, exclusão e visualização de perfil com histórico de contratos e extrato.
  * **Empréstimos:**
    * Simulador em tempo real integrado (cálculo de juros lineares, total financiado e valor da parcela).
    * Geração automática de contratos numerados (`ASB-YYYY-XXXX`) e desdobramento automático de todas as parcelas mensais no banco de dados.
    * **Enviar PIX (Cash-Out):** Disparo instantâneo do valor do empréstimo para a chave PIX do cliente via API MisticPay.
    * **Gerar PIX de Cobrança (Cash-In):** Gera QR Code dinâmico e código Copia e Cola para cada parcela individual.
    * **Contrato Digital & Análise de Crédito:** Emissão de contrato formal com parecer e score de crédito interno (300 a 1000) e visualização de impressão/PDF.
  * **Extrato (Envios e Recebimentos):**
    * Listagem de todas as saídas (desembolsos) e entradas (pagamento de parcelas).
    * Filtros por tipo (*Todos, Apenas Envios, Apenas Recebimentos*), status (*Concluído, Pendente, Falha*), período e busca por cliente/documento/E2E.
    * Totais em tempo real: Total Enviado, Total Recebido, Saldo Líquido e Taxas.
  * **Configurações MisticPay:**
    * Gestão de Chave Pública, Chave Secreta, URL Base, toggle de Modo Simulação e botão de **Testar Conexão API**.

### 2. Portal Exclusivo do Cliente (`/cliente`)
* **Painel do Mutuário:**
  * Limite pré-aprovado, contratos ativos e saldo devedor.
  * **Próximas Parcelas com PIX:** Acesso com 1 clique a *"Pagar via PIX"*, exibindo o QR Code em alta definição e o Copia e Cola com botão para copiar.
  * **Contrato Digital Automático:** Consulta ao instrumento de crédito com termos jurídicos e score de análise de crédito.
  * **Meu Extrato PIX:** Histórico pessoal dos valores recebidos e parcelas pagas.

---

## 💳 Integração MisticPay (PIX e Webhooks)

O serviço está implementado em `App\Services\MisticPayService` e no Webhook `App\Http\Controllers\Api\MisticPayWebhookController`.

### 🔑 Como e onde pegar as chaves na MisticPay:
1. Acesse o painel da MisticPay: **[painel.misticpay.com](https://painel.misticpay.com)** e faça login na sua conta.
2. No menu lateral, acesse **API** > **Chaves de Acesso**.
3. Clique em **Criar Chave de Acesso**.
4. **Habilite obrigatoriamente os seguintes escopos (*Scopes*):**
   * `TRANSACTION_CREATE`: Para gerar cobranças PIX dinâmicas e QR Codes (Cash-In).
   * `WITHDRAW_CREATE`: Para enviar PIX de desembolso para a chave do cliente (Cash-Out).
   * `TRANSACTION_READ`: Para consultar o status das transações.
   * `BALANCE_READ`: Para consultar saldo disponível na conta.
5. Copie a **Chave Pública** (`pk_...`) e a **Chave Secreta** (`sk_...`).
6. Cole no painel em **Gateway MisticPay** (`/admin/configuracoes/misticpay`) ou no `.env`:
   ```env
   MISTICPAY_BASE_URL=https://api.misticpay.com
   MISTICPAY_PUBLIC_KEY=pk_live_...
   MISTICPAY_SECRET_KEY=sk_live_...
   MISTICPAY_MOCK=false
   ```
7. **Cadastrar Webhook na MisticPay:**
   * No painel da MisticPay, acesse **Webhooks** e cadastre a URL:
     ```
     https://seudominio.com.br/api/webhooks/misticpay
     ```
   * Ative os eventos para `DEPOSITO` (recebimento de PIX) e `RETIRADA` (saques/envios).

---

## 🛠️ Tecnologias Utilizadas

* **PHP 8.3+ / 8.5**
* **Laravel 13**
* **Laravel Breeze** (Blade Stack)
* **MySQL**
* **Tailwind CSS** (Tema exclusivo dark banking com as cores da marca ASBANK)
* **Lucide Icons**
* **Simple QrCode (BaconQrCode)**
* **Apache mod_rewrite** (`.htaccess` e `htaccess` na raiz configurados para redirecionar para a pasta `public/`)

---

## 📦 Instalação e Execução Local

1. **Clonar o Repositório:**
   ```bash
   git clone https://github.com/RenanPiccinini/asbank.git
   cd asbank
   ```

2. **Instalar Dependências:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configurar o Ambiente:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure seu banco MySQL no `.env`:*
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=asbank
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Executar Migrações e Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Iniciar o Servidor:**
   ```bash
   php artisan serve
   ```
   Acesse: `http://localhost:8000`

---

## 🔐 Acessos de Demonstração

* **Administrador Geral:**
  * **E-mail:** `admin@asbank.com.br`
  * **Senha:** `admin123`

* **Cliente Mutuário:**
  * **E-mail:** `carlos.silva@email.com`
  * **Senha:** `cliente123`

---

## 🧪 Testes Automatizados

O projeto conta com uma suíte completa de testes automatizados cobrindo autenticação, permissões, criação de clientes, geração de empréstimos, disparos PIX, geração de cobrança, extrato e processamento de webhooks:

```bash
php artisan test
```
*Resultado: 35 testes e 93 asserções com 100% de aprovação.*
