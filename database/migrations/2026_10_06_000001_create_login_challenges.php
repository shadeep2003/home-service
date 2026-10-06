<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        // MySQL DDL is not transactional: a failed table creation may leave this column.
        if (! Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('email_verified_at')->nullable());
        }
        Schema::create('login_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('binding_hash', 64);
            $table->string('account_hash', 64);
            $table->string('otp_hash')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('expires_at');
            $table->dateTime('resend_at');
            $table->dateTime('pending_until')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('login_challenges');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('email_verified_at'));
    }
};
