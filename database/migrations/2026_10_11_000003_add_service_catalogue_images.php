<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        foreach (['service_categories', 'service_subcategories'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('image')->nullable();
                $table->string('image_alt', 255)->nullable();
            });
        }
    }
    public function down(): void {
        foreach (['service_subcategories', 'service_categories'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['image', 'image_alt']));
        }
    }
};
