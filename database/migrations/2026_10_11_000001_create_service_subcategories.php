<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('service_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('name', 100); $table->string('name_key', 100);
            $table->text('description')->nullable(); $table->boolean('is_active')->default(true);
            $table->unique(['service_category_id', 'name_key']); $table->timestamps();
        });
        Schema::create('provider_subcategories', function (Blueprint $table) {
            $table->id(); $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_subcategory_id')->constrained()->restrictOnDelete();
            $table->unique(['provider_id', 'service_subcategory_id']); $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('provider_subcategories'); Schema::dropIfExists('service_subcategories');
    }
};
