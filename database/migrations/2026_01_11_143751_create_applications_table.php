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
        Schema::create("applications", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email")->unique();
            $table->string("password");
            $table->string("profession");
            $table->text("user_justification");
            $table->text("admin_justification")->nullable();
            $table
                ->enum("status", ["Pending", "Approved", "Rejected"])
                ->default("Pending");
            $table->foreignId("decided_by")->nullable()->constrained("users")->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table("users", function (Blueprint $table) {
            $table->foreign("application_id")->references("id")->on("applications")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign(["application_id"]);
        });
        Schema::dropIfExists("applications");
    }
};
