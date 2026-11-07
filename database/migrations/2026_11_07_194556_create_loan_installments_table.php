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
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->enum('status', ['pendente', 'pago', 'atrasado', 'cancelado'])->default('pendente');
            $table->timestamp('paid_at')->nullable();
            
            // Dados de Cobrança PIX (MisticPay Cash-In)
            $table->string('pix_transaction_id')->nullable();
            $table->longText('pix_qr_code')->nullable(); // Copia e Cola
            $table->longText('pix_qr_code_base64')->nullable(); // Imagem base64 do QR Code
            $table->string('pix_e2e')->nullable();
            $table->timestamp('pix_created_at')->nullable();
            $table->timestamp('pix_expires_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
    }
};
