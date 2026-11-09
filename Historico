# ASBANK - Histórico do Projeto e Documentação Técnica do Sistema

> **Atenção para Desenvolvedores e Agentes de IA:**
> Este arquivo é a fonte única de verdade sobre o funcionamento, regras de negócio, estrutura de código, menus, ações e integrações do sistema ASBANK. **Leia este arquivo na íntegra ao abrir qualquer nova janela ou sessão de desenvolvimento.**
> A cada nova alteração, recurso adicionado ou refatoração no sistema, **atualize obrigatoriamente este arquivo** mantendo o histórico de mudanças no final.

---

## 1. Visão Geral do Sistema

O **ASBANK** é uma plataforma bancária e de gestão financeira para correspondentes bancários, fintechs e concessão de empréstimos pessoais e empresariais, com liquidação e desembolso automatizados via **PIX**, integrada ao gateway **MisticPay**.

### Principais Pilares do Sistema
1. **Gerenciador Geral (Painel Administrativo):** Controle completo de clientes, criação de contratos de empréstimo, análise de crédito, envio automático de dinheiro via PIX (Cash-Out / Desembolso), geração de cobranças PIX por parcela (Cash-In) com QR Code dinâmico e Copia e Cola, conciliação bancária e extrato financeiro unificado.
2. **Portal do Cliente (Internet Banking):** Área do tomador do empréstimo com visualização de contratos ativos, cronograma de parcelas, emissão e visualização de QR Code PIX para pagamento com checagem de status em tempo real (polling via Ajax) e emissão de contrato digital assinado.
3. **Integração MisticPay Gateway:** Comunicação via API REST para consulta de saldo, criação de cobranças PIX, ordens de transferência/saque PIX para chaves de clientes (CPF, CNPJ, Telefone, E-mail, Chave Aleatória/EVP) e recepção de webhooks para baixa automática imediata.

---

## 2. Arquitetura e Stack Tecnológica

- **Framework Backend:** Laravel 12 / PHP 8.3+
- **Frontend / UI:** Blade Templates, Tailwind CSS, Vite, Lucide Icons
- **Banco de Dados:** MySQL (configurado em `.env`) / compatível com SQLite e PostgreSQL
- **Autenticação & Controle de Acesso:** Laravel Breeze customizado com controle de perfis por Role (`admin` e `client`) e Middlewares dedicados (`AdminMiddleware` e `ClientMiddleware`)
- **Manipulação de QR Code:** Pacote `simplesoftwareio/simple-qrcode` integrado para renderização SVG/Base64 em tela
- **Serviço de Comunicação HTTP:** `Illuminate\Support\Facades\Http` do Laravel com suporte a timeout e headers personalizados de autenticação

---

## 3. Modelo de Dados & Banco

### 3.1. `User` (Tabela `users`)
- Perfis de acesso: `admin` (gestor financeiro) e `client` (tomador do empréstimo).
- Relaciona-se com `Client` (1 para 1).
- Credenciais padrão de seed:
  - **Admin:** `admin@asbank.com.br` / senha: `admin123`
  - **Cliente Exemplo:** `carlos.silva@email.com` / senha: `cliente123`

### 3.2. `Client` (Tabela `clients`)
- Dados pessoais completos: Nome, CPF/CNPJ, E-mail, Telefone, Data de Nascimento.
- Endereço completo: CEP, Logradouro, Número, Complemento, Bairro, Cidade, UF.
- Dados Bancários e Chave PIX: `bank_name`, `bank_agency`, `bank_account`, `pix_key`, `pix_key_type` (`cpf`, `cnpj`, `email`, `phone`, `random`).
- Financeiro: `monthly_income` (Renda), `credit_limit` (Limite pré-aprovado), `status` (`ativo`, `inativo`, `bloqueado`), `notes`.

### 3.3. `Loan` (Tabela `loans`)
- Identificador do Contrato: Formato `ASB-YYYY-XXXX` (ex: `ASB-2026-1001`).
- Valores: `amount` (Principal), `interest_rate` (Taxa mensal em %), `installments_count` (Nº de parcelas), `total_amount` (Total financiado com juros), `installment_value` (Valor unitário da parcela).
- Status do Empréstimo: `solicitado`, `aprovado`, `ativo`, `quitado`, `inadimplente`, `cancelado`.
- Desembolso PIX (Cash-Out): `disbursement_status` (`nao_enviado`, `processando`, `enviado`, `falha`), `disbursement_transaction_id`, `disbursement_e2e`, `disbursed_at`.
- Termos do Contrato: `contract_terms` (HTML formal gerado automaticamente para impressão e assinatura digital).

### 3.4. `LoanInstallment` (Tabela `loan_installments`)
- Vinculado a um empréstimo (`loan_id`).
- Campos: `installment_number`, `amount`, `due_date`, `status` (`pendente`, `pago`), `paid_at`.
- Dados de Cobrança PIX (Cash-In): `pix_transaction_id`, `pix_qr_code` (Payload Copia e Cola), `pix_qr_code_base64` (Imagem SVG Base64), `pix_e2e`, `pix_created_at`, `pix_expires_at`.

### 3.5. `FinancialTransaction` (Tabela `financial_transactions`)
- Extrato unificado de movimentação financeira:
  - `type`: `cash_out` (saída de dinheiro / empréstimo liberado para o cliente) ou `cash_in` (entrada de dinheiro / pagamento de parcela pelo cliente).
  - `category`, `amount`, `fee` (taxa cobrada pelo gateway), `misticpay_transaction_id`, `e2e`, `pix_key`, `counterparty_name`, `counterparty_document`, `status` (`pendente`, `completo`, `falha`, `cancelado`), `raw_response` (JSON retornado pelo gateway).

---

## 4. Menus, Navegação e Telas do Sistema

### 4.1. Módulo Administrativo (Gerenciador Geral)
Acesso restrito a usuários com perfil `admin` (`/admin`):

1. **Dashboard Financeiro (`/admin`):**
   - Cards de Indicadores (KPIs): Total Emprestado, Total Recebido via PIX, Total a Receber, Total de Clientes Ativos e Contratos.
   - Balanço do Extrato: Resumo consolidado de Envios (Cash-Out) vs Recebimentos (Cash-In).
   - Tabela de Últimos Contratos gerados com status e atalhos rápidos.
   - Tabela de Últimas Transações no extrato com identificação visual por cores (verde para entrada, vermelho para saída).
   - Banner informativo do status do Gateway MisticPay (Modo Simulação vs Produção) alimentado pelas variáveis do `.env`.

2. **Clientes (`/admin/clients`):**
   - Listagem com busca por nome, documento (CPF/CNPJ), e-mail ou telefone.
   - Formulário de cadastro de novos clientes com validação rigorosa e opção de criação automática de usuário de acesso para o portal do cliente.
   - Edição de cadastro, atualização de dados bancários e chave PIX de recebimento.
   - Visualização detalhada (Show) contendo o dossiê do cliente: limites, empréstimos ativos, parcelas pendentes e histórico de pagamentos.

3. **Empréstimos (`/admin/loans`):**
   - Listagem com filtros por status (`ativo`, `aprovado`, `quitado`, etc.) e cliente.
   - Criação de novo empréstimo com cálculo instantâneo de parcelas, juros e cronograma de vencimentos mensais.
   - **Ações disponíveis em cada empréstimo:**
     - `Enviar PIX (Desembolso)`: Aciona `MisticPayService::sendPixWithdraw()` e envia o valor principal do empréstimo diretamente para a chave PIX do cliente. Atualiza o status para `ativo` e `enviado`, criando o registro correspondente no extrato (`cash_out`).
     - `Gerar Cobrança PIX`: Aciona `MisticPayService::createPixCharge()` para uma parcela específica, obtendo QR Code dinâmico e código Copia e Cola.
     - `Baixa Manual`: Marca a parcela como quitada manualmente e registra no extrato.
     - `Simular Webhook`: Dispara internamente a rotina de webhook de recebimento PIX para testes rápidos da liquidação de parcelas e quitação de contratos.
     - `Imprimir Contrato`: Exibe e permite imprimir o Instrumento Particular de Mútuo Financeiro formal com layout bancário de alta qualidade.

4. **Extrato Geral (`/admin/extrato`):**
   - Relatório financeiro detalhado de entradas e saídas.
   - Filtros por tipo de transação (Todos, Apenas Envios PIX, Apenas Recebimentos PIX), período (data inicial e final) e busca textual.
   - Totais consolidados de envios, recebimentos e saldo líquido.
   - Botão para impressão/exportação formatada do extrato.

5. **Acesso Externo / Ver Área do Cliente:**
   - Link direto no menu lateral para visualizar o Portal do Cliente em uma nova aba para testes e homologação.

---

### 4.2. Portal do Cliente (Internet Banking)
Acesso restrito a usuários com perfil `client` (`/cliente`):

1. **Dashboard do Cliente (`/cliente`):**
   - Resumo pessoal: Total em empréstimos tomados, total quitado e total em aberto.
   - Próximas parcelas a vencer com botão de pagamento imediato `Pagar via PIX`.
   - Últimas movimentações da conta.

2. **Meus Empréstimos (`/cliente/emprestimos` e `/cliente/emprestimos/{id}`):**
   - Lista de todos os contratos já firmados com o ASBANK.
   - Detalhe de cada contrato: cronograma de todas as parcelas, datas de vencimento, valores e status de pagamento.
   - Visualização do Contrato Digital (`/cliente/emprestimos/{id}/contrato`).

3. **Tela de Pagamento PIX da Parcela (`/cliente/parcelas/{id}/pagar`):**
   - Renderização em tempo real do QR Code PIX e do código Copia e Cola.
   - Botão "Copiar Chave Copia e Cola" com feedback via toast.
   - **Polling Ajax Inteligente (`/cliente/parcelas/{id}/status`):** A página verifica periodicamente o status da parcela no banco de dados. Assim que o webhook da MisticPay confirmar o pagamento, a tela é atualizada automaticamente para o estado "Pagamento Confirmado" com animação de sucesso, sem necessidade de refresh manual.

4. **Extrato Pessoal (`/cliente/extrato`):**
   - Histórico de todos os desembolsos recebidos pelo cliente e parcelas pagas, com códigos de transação e datas.

---

## 5. Gateway de Pagamento MisticPay (Configuração 100% via `.env`)

As credenciais da MisticPay foram centralizadas exclusivamente no arquivo `.env` do servidor, garantindo segurança corporativa e evitando que chaves de API fiquem expostas ou salvas em tabelas do banco de dados.

### 5.1. Variáveis de Configuração no `.env`

```env
# Gateway MisticPay (PIX)
MISTICPAY_BASE_URL=https://api.misticpay.com
MISTICPAY_PUBLIC_KEY=pk_d3ecbef93caf6b4675b4c1534a0aaf1e
MISTICPAY_SECRET_KEY=sk_sua_chave_secreta_aqui
MISTICPAY_MOCK=false
```

- `MISTICPAY_BASE_URL`: URL base da API da MisticPay (padrão: `https://api.misticpay.com`).
- `MISTICPAY_PUBLIC_KEY`: Chave pública / Client ID fornecida no painel MisticPay.
- `MISTICPAY_SECRET_KEY`: Chave secreta / Client Secret fornecida no painel MisticPay.
- `MISTICPAY_MOCK`: Booleano (`true` ou `false`).
  - Se `false` e as chaves estiverem preenchidas: O sistema opera em **Modo Produção Real**, disparando requisições HTTP reais para os servidores da MisticPay.
  - Se `true` ou se qualquer uma das chaves estiver vazia: O sistema entra automaticamente em **Modo de Simulação (Mock)**, gerando códigos PIX válidos localmente e permitindo testes completos ponta a ponta sem debitar dinheiro real da conta.

### 5.2. Arquitetura da Classe `App\Services\MisticPayService`

- **Construtor (`__construct`):** Lê os dados diretamente de `config('services.misticpay.*')`, que por sua vez obtém os valores do `.env`.
- **`testConnection(): array`:** Realiza uma requisição autenticada de teste contra o endpoint `/api/users/balance` da MisticPay, validando se as chaves estão ativas e se os scopes estão corretos, retornando o saldo disponível.
- **`createPixCharge(...)`: Cash-In:**
  - Endpoint Produção: `POST /api/transactions/create`
  - Headers: `Authorization: Basic {base64(pk:sk)}`, `ci: {pk}`, `cs: {sk}`, `Content-Type: application/json`
  - Retorna o ID da transação, o código PIX Copia e Cola e o QR Code em base64.
- **`sendPixWithdraw(...)`: Cash-Out (Desembolso):**
  - Endpoint Produção: `POST /api/transactions/withdraw`
  - Envia fundos para qualquer tipo de chave PIX cadastrada (`CPF`, `CNPJ`, `EMAIL`, `PHONE`, `EVP`).
- **`handleWebhook(array $payload)`:**
  - Endpoint do Sistema: `POST /api/webhooks/misticpay` (isento de verificação CSRF).
  - Identifica se o evento pertence a um recebimento de parcela ou a um desembolso de empréstimo.
  - Ao receber status `COMPLETO`, quita a parcela, armazena o código End-to-End (`e2e`), registra o movimento no extrato e, caso todas as parcelas tenham sido pagas, altera automaticamente o contrato para `quitado`.

### 5.3. Requisitos de Segurança & Lista de Permissões de IP (IP Whitelist)
- A API da MisticPay exige que o IP do servidor que realiza as chamadas esteja autorizado no painel oficial (`painel.misticpay.com` > **API** > **IPs Autorizados**).
- Em servidores como Hostinger / cPanel que utilizam IPv6 (dual-stack), certifique-se de cadastrar tanto o IPv6 (ex: `2a02:4780:...`) quanto o IPv4 do servidor.
- Para envios reais de PIX (Cash-Out / Desembolso), a conta na MisticPay deve possuir saldo suficiente para cobrir o valor principal + taxa de transferência (R$ 0,50). Caso contrário, a API retornará `"Saldo insuficiente"`.

---

## 6. Como Testar Todas as Funções da API e do Sistema

### 6.1. Teste Automatizado via Terminal (Comando Exclusivo Artisan)

Foi desenvolvido um comando artisan exclusivo para validar toda a esteira da API de forma rápida e clara:

```sh
# Executa todos os testes (Saldo, Cash-In, Cash-Out e Webhook)
php artisan misticpay:test

# Testar apenas a consulta de saldo e credenciais reais
php artisan misticpay:test --type=balance

# Testar apenas a geração de cobrança PIX (Cash-In) com valor customizado
php artisan misticpay:test --type=cashin --amount=25.50

# Testar apenas o envio de PIX (Cash-Out)
php artisan misticpay:test --type=cashout --amount=50.00

# Testar apenas a recepção e o processamento de Webhooks
php artisan misticpay:test --type=webhook
```

### 6.2. Testes de Suíte Automatizada (PHPUnit / Pest)

O sistema possui uma suíte completa de testes de feature e regras de negócio:

```sh
php artisan test
```

Testes inclusos cobrem:
- Acessibilidade da Landing Page
- Acesso e menus do Administrador
- Restrição de acessos e permissões por role
- Criação e validação de clientes com conta automática
- Geração de empréstimos com cronograma de amortização
- Envio de PIX de desembolso (Cash-Out)
- Geração de cobrança PIX para parcelas (Cash-In)
- Auditoria do Extrato Geral
- Painel do Cliente e listagem de contratos
- Processamento e conciliação do Webhook da MisticPay
- Execução do comando `misticpay:test`

### 6.3. Teste Manual pelo Painel Administrativo

1. Inicie a aplicação:
   ```sh
   php artisan serve
   ```
2. Acesse `http://localhost:8000/login` e entre como administrador:
   - **E-mail:** `admin@asbank.com.br`
   - **Senha:** `admin123`
3. **Testar Desembolso PIX (Cash-Out):**
   - Acesse o menu **Empréstimos**.
   - Abra um empréstimo com status "Não Enviado".
   - Clique no botão **Liberar Empréstimo via PIX (Cash-Out)**.
   - O sistema disparará a chamada para a MisticPay e registrará a saída no **Extrato Geral**.
4. **Testar Cobrança PIX (Cash-In):**
   - Na listagem de parcelas do empréstimo, clique no botão **Gerar PIX**.
   - O QR Code dinâmico e o código Copia e Cola serão gerados.
5. **Testar Liquidação Automática:**
   - Na parcela, clique no botão **Simular Webhook** (ou efetue o pagamento real se estiver em produção).
   - O sistema mudará o status da parcela para **Pago**, registrará o End-to-End e atualizará o extrato financeiro.

### 6.4. Teste via cURL / Postman / Insomnia (Webhook)

Você pode simular o envio de um webhook idêntico ao disparado pelos servidores da MisticPay para o seu servidor local:

```sh
curl -X POST http://localhost:8000/api/webhooks/misticpay \
  -H "Content-Type: application/json" \
  -d '{
    "transactionId": "ID_DA_TRANSACAO_AQUI",
    "transactionType": "DEPOSITO",
    "transactionMethod": "PIX",
    "status": "COMPLETO",
    "value": 150.00,
    "fee": 1.50,
    "e2e": "E20261109153000ASBANKTEST123456",
    "clientName": "Carlos Eduardo Silva",
    "clientDocument": "12345678901"
  }'
```

---

## 7. Histórico de Alterações e Atualizações

### [2026-11-09] - Centralização de Credenciais no `.env` & Documentação Centralizada
- **Remoção do Menu de Configuração:**
  - Removido o item de menu "Gateway MisticPay" do sidebar administrativo (`resources/views/layouts/admin.blade.php`).
  - Removida a tela `resources/views/admin/settings/misticpay.blade.php` e a dependência do controller legado.
  - Criado redirecionamento gracioso para a rota legada `/admin/configuracoes/misticpay`, redirecionando direto para o Dashboard.
- **Credenciais 100% no `.env`:**
  - Ajustado `App\Services\MisticPayService` para consumir exclusivamente as configurações de `config/services.php`, eliminando consultas à tabela `gateway_settings` no banco de dados.
  - Atualizados `.env` e `.env.example` com as variáveis padrão da MisticPay (`MISTICPAY_BASE_URL`, `MISTICPAY_PUBLIC_KEY`, `MISTICPAY_SECRET_KEY`, `MISTICPAY_MOCK`).
- **Comando de Diagnóstico e Teste da API:**
  - Criado o comando `php artisan misticpay:test` que diagnostica credenciais, consulta saldo da conta MisticPay (`/api/users/balance`), testa criação de cobrança PIX (Cash-In), teste de envio PIX (Cash-Out) e simulação de webhook.
- **Criação do Documento `HISTORICO.md`:**
  - Documentação completa de regras, arquitetura, menus, modelos, fluxos de empréstimos, webhooks e procedimentos de teste para guiar futuros agentes e desenvolvedores.
- **Suíte de Testes:**
  - Testes do PHPUnit atualizados e 100% aprovados (36 testes, 96 asserções).
