<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mariadb';

    public function up(): void
    {
        Schema::connection('mariadb')->create('apm_activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50)->index();       // bpjs | database | fingerprint | frista | system
            $table->string('event', 100)->index();     // nama event/aksi
            $table->enum('status', ['success', 'error'])->index();
            $table->text('message');
            $table->json('context')->nullable();       // data tambahan (opsional)
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::connection('mariadb')->dropIfExists('apm_activity_logs');
    }
};
