<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'client_id',
        'amount',
        'interest_rate',
        'installments_count',
        'total_amount',
        'installment_value',
        'first_due_date',
        'status',
        'origin',
        'loan_purpose',
        'credit_score',
        'credit_analysis_notes',
        'pix_key',
        'pix_key_type',
        'disbursement_status',
        'disbursement_transaction_id',
        'disbursement_e2e',
        'disbursed_at',
        'contract_terms',
        'created_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'installment_value' => 'decimal:2',
            'first_due_date' => 'date',
            'disbursed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class)->orderBy('installment_number');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class)->orderBy('created_at', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function paidInstallmentsCount(): int
    {
        return $this->installments()->where('status', 'pago')->count();
    }

    public function pendingInstallmentsCount(): int
    {
        return $this->installments()->where('status', '!=', 'pago')->count();
    }

    public function totalPaidAmount(): float
    {
        return (float) $this->installments()->where('status', 'pago')->sum('amount');
    }

    public function totalRemainingAmount(): float
    {
        return (float) $this->installments()->where('status', '!=', 'pago')->sum('amount');
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'aprovado' => ['label' => 'Aprovado', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
            'ativo' => ['label' => 'Ativo / Em Andamento', 'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
            'quitado' => ['label' => 'Quitado', 'class' => 'bg-emerald-600/20 text-emerald-300 border-emerald-500/30'],
            'em_analise' => ['label' => 'Em Análise', 'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
            'rejeitado' => ['label' => 'Rejeitado', 'class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20'],
        };
    }

    public function disbursementBadge(): array
    {
        return match ($this->disbursement_status) {
            'enviado' => ['label' => 'PIX Enviado', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
            'processando' => ['label' => 'Processando Envio', 'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
            'falha' => ['label' => 'Falha no Envio', 'class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'],
            default => ['label' => 'Não Enviado', 'class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20'],
        };
    }

    /**
     * Generate sequential contract number ASB-YYYY-XXXX
     */
    public static function generateContractNumber(): string
    {
        $year = date('Y');
        $lastLoan = static::whereYear('created_at', $year)->latest('id')->first();
        $sequence = $lastLoan ? ($lastLoan->id + 1001) : 1001;
        $candidate = sprintf('ASB-%s-%04d', $year, $sequence);

        while (static::where('contract_number', $candidate)->exists()) {
            $sequence++;
            $candidate = sprintf('ASB-%s-%04d', $year, $sequence);
        }

        return $candidate;
    }

    /**
     * Build standard loan installment schedule rows
     */
    public function generateInstallments(): void
    {
        if ($this->installments()->exists()) {
            return;
        }

        $count = max(1, (int) $this->installments_count);
        $installmentValue = round((float) $this->total_amount / $count, 2);
        $roundingDiff = round((float) $this->total_amount - ($installmentValue * $count), 2);
        $firstDueDate = $this->first_due_date ? Carbon::parse($this->first_due_date) : now()->addMonth();

        for ($i = 1; $i <= $count; $i++) {
            $dueDate = (clone $firstDueDate)->addMonths($i - 1);
            $parcelAmount = $installmentValue;
            if ($i === $count && $roundingDiff != 0) {
                $parcelAmount += $roundingDiff;
            }

            LoanInstallment::create([
                'loan_id' => $this->id,
                'installment_number' => $i,
                'amount' => $parcelAmount,
                'due_date' => $dueDate->toDateString(),
                'status' => 'pendente',
            ]);
        }
    }

    /**
     * Generate formal contract terms HTML
     */
    public static function buildStandardContractTerms(
        Client $client,
        string $contractNumber,
        float $amount,
        float $total,
        int $count,
        float $parcelValue,
        float $rate
    ): string {
        $doc = $client->formattedDocument();
        $date = date('d/m/Y');
        $fmtAmount = number_format($amount, 2, ',', '.');
        $fmtTotal = number_format($total, 2, ',', '.');
        $fmtParcel = number_format($parcelValue, 2, ',', '.');

        return <<<HTML
<div class="contract-body space-y-4 text-justify text-sm leading-relaxed text-slate-300">
    <p><strong>INSTRUMENTO PARTICULAR DE CONTRATO DE MÚTUO FINANCEIRO COM LIQUIDAÇÃO EM PARCELAS PIX</strong></p>

    <p><strong>CONTRATO Nº {$contractNumber}</strong></p>

    <p><strong>1. DAS PARTES:</strong><br>
    <strong>CREDORA:</strong> ASBANK SERVIÇOS FINANCEIROS LTDA, instituição financeira privada e correspondente bancário digital, doravante denominada simplesmente <strong>ASBANK</strong>.<br>
    <strong>DEVEDOR(A):</strong> {$client->name}, inscrito(a) no CPF/CNPJ sob o nº {$doc}, com chave PIX cadastrada ({$client->pix_key_type}: {$client->pix_key}), doravante denominado(a) <strong>MUTUÁRIO(A)</strong>.</p>

    <p><strong>2. DO OBJETO E VALORES:</strong><br>
    A CREDORA concede ao(à) MUTUÁRIO(A), a título de mútuo financeiro, o valor principal líquido de <strong>R$ {$fmtAmount}</strong>, a ser transferido diretamente para a chave PIX informada pelo mutuário, mediante aprovação do comitê de crédito.</p>

    <p><strong>3. DO PAGAMENTO E ENCARGOS:</strong><br>
    O montante total financiado, acrescido de taxa de remuneração de {$rate}% a.m., totaliza <strong>R$ {$fmtTotal}</strong>, dividido em <strong>{$count} parcelas</strong> fixas e sucessivas de <strong>R$ {$fmtParcel}</strong>, com vencimentos descritos no cronograma anexo.</p>

    <p><strong>4. DA LIQUIDAÇÃO VIA PIX:</strong><br>
    As parcelas serão quitadas mediante QR Code PIX ou chave PIX Copia e Cola disponibilizados no portal do cliente e enviados via notificação eletrônica pela ASBANK.</p>

    <p><strong>5. ANÁLISE DE CRÉDITO E CONFORMIDADE:</strong><br>
    Este contrato foi emitido após validação biométrica e consulta automatizada aos birôs de crédito, com parecer favorável do sistema antifraude ASBANK.</p>

    <p class="pt-4">Emitido digitalmente pelo sistema ASBANK em {$date}.</p>
</div>
HTML;
    }

    /**
     * WhatsApp links tailored to each stage of credit analysis
     */
    public function whatsappContactUrl(): string
    {
        $firstName = explode(' ', trim($this->client->name))[0];
        $amountFmt = number_format((float) $this->amount, 2, ',', '.');

        $msg = "Olá, *{$firstName}*! Aqui é da equipe de Análise de Crédito do *ASBANK*.\n\n"
             . "Estamos analisando sua solicitação *{$this->contract_number}* no valor de *R$ {$amountFmt}* em *{$this->installments_count}x*.\n\n"
             . "Podemos conversar rapidamente para finalizar seu atendimento?";

        return $this->client->whatsappUrl($msg);
    }

    public function whatsappProposalUrl(): string
    {
        $firstName = explode(' ', trim($this->client->name))[0];
        $amountFmt = number_format((float) $this->amount, 2, ',', '.');
        $parcelFmt = number_format((float) $this->installment_value, 2, ',', '.');
        $totalFmt = number_format((float) $this->total_amount, 2, ',', '.');
        $dueFmt = $this->first_due_date ? $this->first_due_date->format('d/m/Y') : now()->addMonth()->format('d/m/Y');

        $msg = "Olá, *{$firstName}*! Aqui é da equipe *ASBANK*.\n\n"
             . "Temos uma *condição especial* preparada para sua proposta *{$this->contract_number}*:\n\n"
             . "💰 *Valor Liberado via PIX:* R$ {$amountFmt}\n"
             . "📅 *Parcelamento:* {$this->installments_count}x de R$ {$parcelFmt}\n"
             . "📊 *Taxa:* {$this->interest_rate}% a.m.\n"
             . "🧾 *Total Final:* R$ {$totalFmt}\n"
             . "🗓️ *1º Vencimento:* {$dueFmt}\n\n"
             . "Você confirma essas condições para seguirmos com a aprovação e liberação do seu acesso?";

        return $this->client->whatsappUrl($msg);
    }

    public function whatsappApprovalUrl(): string
    {
        $firstName = explode(' ', trim($this->client->name))[0];
        $amountFmt = number_format((float) $this->amount, 2, ',', '.');
        $parcelFmt = number_format((float) $this->installment_value, 2, ',', '.');
        $loginUrl = route('login');

        $msg = "🎉 *Parabéns, {$firstName}!* Sua proposta de crédito no *ASBANK* foi *APROVADA*!\n\n"
             . "📄 *Contrato:* {$this->contract_number}\n"
             . "💰 *Valor Aprovado:* R$ {$amountFmt}\n"
             . "📅 *Parcelas:* {$this->installments_count}x de R$ {$parcelFmt}\n"
             . "⚡ *Chave PIX para liberação:* {$this->pix_key}\n\n"
             . "🔓 *Seu acesso ao Portal do Cliente já foi liberado!*\n"
             . "🌐 Acesse: {$loginUrl}\n"
             . "📧 E-mail de acesso: {$this->client->email}\n\n"
             . "Qualquer dúvida, estamos à disposição!";

        return $this->client->whatsappUrl($msg);
    }

    public function whatsappRejectionUrl(): string
    {
        $firstName = explode(' ', trim($this->client->name))[0];
        $msg = "Olá, *{$firstName}*. Aqui é da equipe *ASBANK*.\n\n"
             . "Agradecemos pelo seu interesse em nossa plataforma (Proposta *{$this->contract_number}*). "
             . "No momento, nossa política de crédito não permitiu a aprovação da operação nas condições solicitadas.\n\n"
             . "Você poderá realizar uma nova simulação futuramente ou falar conosco por aqui para verificar outras opções.";

        return $this->client->whatsappUrl($msg);
    }
}
