<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('telegram_chat_id')->nullable(); 
            $table->timestamps();
        });

        DB::statement('ALTER TABLE products ADD COLUMN location geography(Point, 4326)');
        DB::statement('CREATE INDEX products_location_idx ON products USING GIST (location)');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};