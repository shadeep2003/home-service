<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id(); $table->string('name', 100)->unique(); $table->string('slug', 120)->unique();
            $table->text('description')->nullable(); $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('phone', 30); $table->string('service_area', 255);
            $table->text('biography')->nullable(); $table->unsignedTinyInteger('experience_years')->nullable();
            $table->string('working_hours', 255)->nullable(); $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id(); $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->unique(['provider_id', 'service_category_id']); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('provider_services'); Schema::dropIfExists('provider_profiles'); Schema::dropIfExists('service_categories');
    }
};
