<?php

use App\Models\Payments\Enums\PaymentMethod;
use App\Models\Payments\Enums\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', PaymentMethod::values());
            $table->nullableMorphs('payable');
            $table->string('currency')->default('ARS');
            $table->string('payment_code')->nullable(); // codigo generado por el pago
            $table->string('reference')->unique();
            $table->float('amount');
            $table->enum('status', PaymentStatus::values());
            $table->string('description')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
