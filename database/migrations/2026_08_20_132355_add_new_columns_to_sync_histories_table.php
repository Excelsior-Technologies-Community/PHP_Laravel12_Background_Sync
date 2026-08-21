<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_histories', function (Blueprint $table) {
            $table->integer('progress')->default(0)->after('duration');
            $table->string('sync_type')->default('General')->after('progress');
            $table->date('date_range_start')->nullable()->after('sync_type');
            $table->date('date_range_end')->nullable()->after('date_range_start');
            $table->boolean('is_paused')->default(false)->after('date_range_end');
            $table->boolean('is_cancelled')->default(false)->after('is_paused');
            $table->text('log_details')->nullable()->after('is_cancelled');
        });
    }

    public function down(): void
    {
        Schema::table('sync_histories', function (Blueprint $table) {
            $table->dropColumn(['progress', 'sync_type', 'date_range_start', 'date_range_end', 'is_paused', 'is_cancelled', 'log_details']);
        });
    }
};
