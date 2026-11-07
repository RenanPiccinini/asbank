<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'loan_id',
        'installment_id',
        'type', // cash_out (Envio PIX), cash_in (Recebimento PIX)
        'category',
        'amount',
        'fee',
        'misticpay_transaction_id',
        'e2e',
        'pix_key',
        'pix_key_type',
        'counterparty_name',
        'counterparty_document',
        'status', // pendente, completo, falha, cancelado
        'description',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'raw_response' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(LoanInstallment::class, 'installment_id');
    }

    public function typeBadge(): array
    {
        if ($this->type === 'cash_out') {
            return [
                'label' => 'Envio PIX (Saída)',
                'icon' => 'arrow-up-right',
                'class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                'sign' => '-',
                'color' => 'text-rose-400',
            ];
        }
        return [
            'label' => 'Recebimento PIX (Entrada)',
            'icon' => 'arrow-down-left',
            'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'sign' => '+',
            'color' => 'text-emerald-400',
        ];
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'completo' => ['label' => 'Concluído', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
            'pendente' => ['label' => 'Pendente', 'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
            'falha' => ['label' => 'Falhou', 'class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20'],
        };
    }
}

