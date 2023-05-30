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
            $table->enum('voyageType', ['ROUNDTRIP', 'ONEWAY', 'DAYTRIP']);
            $table->string('voyageReferenceNumber');
            $table->boolean('isPassportRequired')->default(1);
            $table->integer('embarkPortId')->nullable();
            $table->date('startDate');
            $table->time('startTime');
            $table->integer('disembarkPortId')->nullable();
            $table->date('endDate');
            $table->time('endTime');
            $table->integer('companyId');
            $table->enum('voyageStatus', ['DRAFT', 'ACTIVE', 'CANCELLED'])
                  ->default('ACTIVE');
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
