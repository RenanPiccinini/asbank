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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('status', 30)->default('ativo')->change();
            $table->string('rg', 30)->nullable()->after('document');
            $table->string('occupation', 120)->nullable()->after('monthly_income');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('origin', 20)->default('admin')->after('status'); // admin, site
            $table->string('loan_purpose', 150)->nullable()->after('origin');
            $table->foreignId('reviewed_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['origin', 'loan_purpose', 'reviewed_by', 'reviewed_at', 'rejection_reason']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['rg', 'occupation']);
        });
    }
};
