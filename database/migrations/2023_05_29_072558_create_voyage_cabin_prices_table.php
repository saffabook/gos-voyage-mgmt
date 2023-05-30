<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoyageCabinPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voyage_cabin_prices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('cabinId');
            $table->integer('voyageId');
            $table->string('currency');
            $table->integer('priceMinor');
            $table->integer('discountedPriceMinor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voyage_cabin_prices');
    }
}
