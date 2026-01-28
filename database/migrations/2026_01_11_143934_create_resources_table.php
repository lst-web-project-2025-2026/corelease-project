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
        Schema::create("resources", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->foreignId("category_id")->constrained()->onDelete("cascade");
            $table->json("specs"); // Stores CPU, RAM, etc.
            $table->enum("status", ["Enabled", "Disabled", "Maintenance"])->default("Enabled");
            $table->foreignId("supervisor_id")->nullable()->constrained("users")->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("resources");
    }
};
