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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            
            // Valores e Parcelas
            $table->decimal('amount', 12, 2); // Valor solicitado
            $table->decimal('interest_rate', 5, 2)->default(2.50); // % ao mês
            $table->integer('installments_count')->default(12);
            $table->decimal('total_amount', 12, 2); // Valor total com juros
            $table->decimal('installment_value', 12, 2);
            $table->date('first_due_date');
            
            // Análise e Status
            $table->enum('status', ['em_analise', 'aprovado', 'ativo', 'quitado', 'rejeitado', 'cancelado'])->default('aprovado');
            $table->integer('credit_score')->default(820);
            $table->text('credit_analysis_notes')->nullable();
            
            // Desembolso via PIX (Cash-Out ao cliente)
            $table->string('pix_key')->nullable();
            $table->string('pix_key_type')->nullable();
            $table->enum('disbursement_status', ['nao_enviado', 'processando', 'enviado', 'falha'])->default('nao_enviado');
            $table->string('disbursement_transaction_id')->nullable();
            $table->string('disbursement_e2e')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            
            // Contrato
            $table->longText('contract_terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
