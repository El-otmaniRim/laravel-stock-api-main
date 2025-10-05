<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add scheduled date if it doesn't exist
            if (!Schema::hasColumn('orders', 'scheduled_date')) {
                $table->timestamp('scheduled_date')->nullable()->after('delivery_id');
            }

            // Add notes if it doesn't exist
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('scheduled_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'scheduled_date')) {
                $table->dropColumn('scheduled_date');
            }

            if (Schema::hasColumn('orders', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
