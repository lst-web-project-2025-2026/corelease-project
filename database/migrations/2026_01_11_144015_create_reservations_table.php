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
        Schema::create("reservations", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table
                ->foreignId("resource_id")
                ->constrained()
                ->onDelete("cascade");
            $table->date("start_date");
            $table->date("end_date");
            $table->text("user_justification");
            $table->text("manager_justification")->nullable();
            $table->json("configuration")->nullable(); // User's specific choices (OS, etc.)
            $table
                ->enum("status", [
                    "Pending",
                    "Approved",
                    "Rejected",
                    "Active",
                    "Completed",
                    "Expired",
                    "Cancelled",
                ])
                ->default("Pending");
            $table->foreignId("decided_by")->nullable()->constrained("users")->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reservations");
    }
};
