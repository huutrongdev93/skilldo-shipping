<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if(!schema()->hasTable('shipping_fee'))
        {
            schema()->create('shipping_fee', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('type', 200)->collate('utf8mb4_unicode_ci')->default('price');
                $table->text('range')->nullable();
                $table->integer('fee')->default(0);
                $table->tinyInteger('default')->default(0);
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated')->nullable();
            });
        }

        if(!schema()->hasTable('shipping_zones'))
        {
            schema()->create('shipping_zones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->integer('feeId')->default(0);
                $table->integer('city')->default(0);
                $table->integer('ward')->default(0);
                $table->tinyInteger('wardOption')->default(1);
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated')->nullable();
            });
        }
    }

    public function down(): void
    {
        schema()->drop('shipping_fee');
        schema()->drop('shipping_zones');
    }
};
