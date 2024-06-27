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
        Schema::create('n_sidebars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('access');
            $table->string('route')->nullable();
            $table->boolean('is_parent')->nullable();
            $table->foreignIdFor(\App\Models\Backend\NSidebar::class)->nullable();
            $table->unsignedBigInteger('serial');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n_sidebars');
    }
};
