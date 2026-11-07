<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Contrato Digital ASBANK - {{ $loan->contract_number }}</title>
    <style>
        @media print {
            body { background: white !important; color: black !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 30px;
            font-size: 13px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header img {
            height: 50px;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            background: #f1f5f9;
            padding: 8px 12px;
            border-left: 4px solid #0284c7;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background: #f8fafc;
            font-weight: bold;
        }
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #475569;
            padding-top: 8px;
            font-size: 12px;
        }
        .btn-print {
            background: #0284c7;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 800px; margin: 0 auto 15px auto; text-align: right;">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Salvar em PDF</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK">
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Confiança que cresce • ASBANK Serviços Financeiros</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 16px; font-weight: bold; color: #0284c7;">CONTRATO DIGITAL</div>
                <div style="font-family: monospace; font-size: 14px; font-weight: bold;">{{ $loan->contract_number }}</div>
                <div style="font-size: 11px; color: #64748b;">Emissão: {{ $loan->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="title">
            INSTRUMENTO PARTICULAR DE CONTRATO DE MÚTUO FINANCEIRO COM LIQUIDAÇÃO EM PARCELAS PIX
        </div>

        <!-- Partes -->
        <div class="section-title">1. Qualificação das Partes</div>
        <p>
            <strong>CREDORA:</strong> ASBANK SERVIÇOS FINANCEIROS LTDA, inscrita no CNPJ sob o nº 00.000.000/0001-99, com sede em São Paulo/SP, doravante denominada simplesmente <strong>ASBANK</strong>.<br>
            <strong>MUTUÁRIO(A):</strong> {{ $loan->client->name }}, inscrito(a) no CPF/CNPJ sob o nº {{ $loan->client->formattedDocument() }}, residente em {{ $loan->client->address }}, {{ $loan->client->number }} - {{ $loan->client->city }}/{{ $loan->client->state }}, Chave PIX cadastrada: {{ strtoupper($loan->client->pix_key_type) }} {{ $loan->client->pix_key }} ({{ $loan->client->bank_name ?: 'Conta Titular' }}).
        </p>

        <!-- Condições da Operação -->
        <div class="section-title">2. Resumo da Operação Financeira</div>
        <div class="grid-2">
            <div>
                <strong>Valor do Empréstimo Solicitado:</strong> R$ {{ number_format($loan->amount, 2, ',', '.') }}<br>
                <strong>Taxa de Remuneração / Juros:</strong> {{ $loan->interest_rate }}% ao mês<br>
                <strong>Número de Parcelas:</strong> {{ $loan->installments_count }} parcelas mensais
            </div>
            <div>
                <strong>Valor Total Financiado a Pagar:</strong> R$ {{ number_format($loan->total_amount, 2, ',', '.') }}<br>
                <strong>Valor de Cada Parcela:</strong> R$ {{ number_format($loan->installment_value, 2, ',', '.') }}<br>
                <strong>Primeiro Vencimento:</strong> {{ $loan->first_due_date->format('d/m/Y') }}
            </div>
        </div>

        <!-- Parecer de Análise de Crédito -->
        <div class="section-title">3. Análise de Crédito e Score Interno</div>
        <p>
            <strong>Score Aprovado:</strong> {{ $loan->credit_score }} pontos (Classificação de Risco Favorável).<br>
            <strong>Parecer do Comitê:</strong> {{ $loan->credit_analysis_notes ?: 'Operação aprovada automaticamente com base nas diretrizes do comitê de crédito e cadastro biométrico antifraude ASBANK.' }}
        </p>

        <!-- Cronograma de Parcelas -->
        <div class="section-title">4. Cronograma de Vencimento e Parcelas</div>
        <table>
            <thead>
                <tr>
                    <th>Parcela</th>
                    <th>Data de Vencimento</th>
                    <th>Valor Nominal (R$)</th>
                    <th>Forma de Pagamento</th>
                    <th>Status Atual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loan->installments as $inst)
                <tr>
                    <td><strong>#{{ $inst->installment_number }}</strong></td>
                    <td>{{ $inst->due_date->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($inst->amount, 2, ',', '.') }}</td>
                    <td>PIX Dinâmico QR Code / Copia e Cola</td>
                    <td><strong>{{ strtoupper($inst->displayStatus()) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Cláusulas Legais -->
        <div class="section-title">5. Cláusulas Gerais</div>
        <p style="text-align: justify; font-size: 11px;">
            <strong>CLÁUSULA PRIMEIRA - DA LIBERAÇÃO DOS RECURSOS:</strong> O valor pactuado neste contrato será transferido pela CREDORA diretamente à chave PIX indicada pelo MUTUÁRIO, servindo o comprovante de liquidação bancária End-to-End como recibo irrevogável de entrega dos recursos.<br>
            <strong>CLÁUSULA SEGUNDA - DA LIQUIDAÇÃO:</strong> As parcelas deverão ser quitadas pelo MUTUÁRIO nas datas aprazadas mediante o QR Code PIX disponibilizado em sua área de cliente no portal digital ASBANK.<br>
            <strong>CLÁUSULA TERCEIRA - DA MORA:</strong> O atraso no pagamento sujeitará o mutuário a multa moratória de 2% (dois por cento) sobre o valor da parcela em atraso, juros de mora legais e eventual inclusão restritiva cadastral após notificação prévia.<br>
            <strong>CLÁUSULA QUARTA - VALIDADE JURÍDICA:</strong> As partes reconhecem a plena validade jurídica das assinaturas eletrônicas e registros digitais deste instrumento, nos termos da MP 2.200-2/2001.
        </p>

        <!-- Assinaturas -->
        <div class="signatures">
            <div>
                <div class="sig-line">
                    <strong>ASBANK SERVIÇOS FINANCEIROS LTDA</strong><br>
                    Certificação Digital Autenticada
                </div>
            </div>
            <div>
                <div class="sig-line">
                    <strong>{{ $loan->client->name }}</strong><br>
                    CPF: {{ $loan->client->formattedDocument() }}
                </div>
            </div>
        </div>

        <div style="text-align: center; font-size: 10px; color: #94a3b8; margin-top: 30px;">
            Documento emitido digitalmente pela plataforma ASBANK em {{ date('d/m/Y \à\s H:i:s') }}. Hash de integridade: {{ hash('sha256', $loan->contract_number . $loan->created_at) }}
        </div>
    </div>

</body>
</html>

