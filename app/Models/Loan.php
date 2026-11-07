<?php

namespace App\Models;

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
}

