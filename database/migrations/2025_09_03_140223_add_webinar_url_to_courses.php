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
        Schema::table('courses', function (Blueprint $table) {
            //
            $table->string('webinar_url')->nullable()->after('description');
            $table->string('webinar_password')->nullable()->after('webinar_url');
            $table->string('webinar_id')->nullable()->after('webinar_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
            $table->dropColumn(['webinar_url',  'webinar_password', 'webinar_id']);
        });
    }
};
