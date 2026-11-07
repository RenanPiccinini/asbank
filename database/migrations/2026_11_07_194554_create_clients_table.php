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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('document')->unique(); // CPF ou CNPJ
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            
            // Endereço
            $table->string('zip_code')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            
            // Dados Bancários / PIX
            $table->string('pix_key')->nullable();
            $table->string('pix_key_type')->default('cpf'); // cpf, cnpj, email, phone, random
            $table->string('bank_name')->nullable();
            $table->string('bank_agency')->nullable();
            $table->string('bank_account')->nullable();
            
            // Perfil Financeiro
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->decimal('credit_limit', 12, 2)->default(10000.00);
            $table->enum('status', ['ativo', 'inativo', 'bloqueado'])->default('ativo');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
