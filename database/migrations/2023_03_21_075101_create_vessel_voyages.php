<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVesselVoyages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vessel_voyages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title')->unique();
            $table->longtext('description');
            $table->integer('vesselId');
            $table->string('voyageType');
            $table->string('voyageReferenceNumber');
            $table->boolean('isPassportRequired')->default(1);
            $table->integer('embarkPortId');
            $table->date('startDate');
            $table->time('startTime');
            $table->integer('disembarkPortId');
            $table->date('endDate');
            $table->time('endTime');
            $table->integer('companyId')->default(0);
            $table->enum('voyageStatus', ['active', 'cancelled'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vessel_voyages');
    }
}
