<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        DB::transaction(function () {
            DB::table('provider_services')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $matched = DB::table('provider_subcategories')->join('service_subcategories', 'service_subcategories.id', '=', 'provider_subcategories.service_subcategory_id')
                        ->where('provider_id', $row->provider_id)->where('service_category_id', $row->service_category_id)->exists();
                    if ($matched) { continue; }
                    $category = DB::table('service_categories')->find($row->service_category_id);
                    $name = mb_substr('General '.$category->name, 0, 100); $key = mb_strtolower($name);
                    DB::table('service_subcategories')->insertOrIgnore(['service_category_id' => $category->id, 'name' => $name, 'name_key' => $key, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
                    $id = DB::table('service_subcategories')->where('service_category_id', $category->id)->where('name_key', $key)->value('id');
                    DB::table('provider_subcategories')->insertOrIgnore(['provider_id' => $row->provider_id, 'service_subcategory_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
                }
            });
        });
    }
    // Associations cannot be distinguished from later provider edits; preserve them on rollback.
    public function down(): void {}
};
