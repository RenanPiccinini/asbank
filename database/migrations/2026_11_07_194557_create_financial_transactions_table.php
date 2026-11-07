<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained('loan_installments')->nullOnDelete();
            
            // Tipo de movimentação: cash_out (Envio PIX), cash_in (Recebimento PIX)
            $table->enum('type', ['cash_out', 'cash_in']);
            $table->string('category')->default('Transação PIX'); // Desembolso de Empréstimo, Pagamento de Parcela, etc.
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0.00);
            
            // Integração MisticPay
            $table->string('misticpay_transaction_id')->nullable();
            $table->string('e2e')->nullable();
            $table->string('pix_key')->nullable();
            $table->string('pix_key_type')->nullable();
            $table->string('counterparty_name')->nullable(); // Nome do recebedor ou pagador
            $table->string('counterparty_document')->nullable(); // CPF/CNPJ
            
            $table->enum('status', ['pendente', 'completo', 'falha', 'cancelado'])->default('pendente');
            $table->text('description')->nullable();
            $table->json('raw_response')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
