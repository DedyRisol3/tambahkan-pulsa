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
        Schema::create('pulsa_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('phone_number');           // Nomor HP tujuan
            $table->string('provider');               // Provider (Telkomsel, XL, Indosat, dll)
            $table->integer('points_used');           // Jumlah poin yang digunakan
            $table->integer('pulsa_amount');          // Nominal pulsa yang didapat (Rp)
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();  // Catatan admin saat proses
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pulsa_redemptions');
    }
};
