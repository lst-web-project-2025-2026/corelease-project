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
        Schema::create("maintenances", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("resource_id")
                ->constrained()
                ->onDelete("cascade");
            $table->foreignId("user_id")->constrained(); // Manager/Admin who created it
            $table->date("start_date");
            $table->date("end_date");
            $table->text("description");
            $table->enum("status", ["Scheduled", "In Progress", "Completed"])->default("Scheduled");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("maintenances");
    }
};
