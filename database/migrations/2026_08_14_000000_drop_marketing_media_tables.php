<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('marketing_media_stock_usage_items');
        Schema::dropIfExists('marketing_media_stock_usages');
        Schema::dropIfExists('marketing_media_stock_request_items');
        Schema::dropIfExists('marketing_media_stock_requests');
        Schema::dropIfExists('marketing_media_division_stock_settings');
        Schema::dropIfExists('marketing_media_division_stocks');
        Schema::dropIfExists('marketing_media_items');
        Schema::dropIfExists('marketing_media_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse — tables and data are permanently deleted.
    }
};
