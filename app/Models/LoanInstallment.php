<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'pix_transaction_id',
        'pix_qr_code',
        'pix_qr_code_base64',
        'pix_e2e',
        'pix_created_at',
        'pix_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'pix_created_at' => 'datetime',
            'pix_expires_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'installment_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pendente' && $this->due_date->isPast();
    }

    public function displayStatus(): string
    {
        if ($this->status === 'pago') {
            return 'Pago';
        }
        if ($this->isOverdue()) {
            return 'Em Atraso';
        }
        return 'Pendente';
    }

    public function statusBadgeClass(): string
    {
        if ($this->status === 'pago') {
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        }
        if ($this->isOverdue()) {
            return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        }
        return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
    }
}

