<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->foreignId('income_policy_id')->nullable()->after('is_visible')
                ->constrained('income_policies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->dropForeign(['income_policy_id']);
            $table->dropColumn('income_policy_id');
        });
    }
};
