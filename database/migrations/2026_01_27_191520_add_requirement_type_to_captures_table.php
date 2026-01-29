<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('captures', function (Blueprint $table) {
            $table->string('requirement_type')->default('bomberos')->after('requirement_number');
            $table->index(['requirement_type', 'year', 'requirement_number'], 'idx_requirement_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captures', function (Blueprint $table) {
            $table->dropIndex('idx_requirement_lookup');
            $table->dropColumn('requirement_type');
        });
    }
};
