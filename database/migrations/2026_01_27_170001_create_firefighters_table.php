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
        Schema::create('firefighters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->string('contact_number')->nullable();
            $table->string('credential_photo_path')->nullable();
            $table->string('geolocation')->nullable();
            $table->string('previous_firefighter')->nullable();
            $table->date('change_date')->nullable();
            $table->decimal('max_rounding_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firefighters');
    }
};
