<?php

namespace Tests\Feature;

use App\Helpers\GenerateVoyageId;
use App\Models\Vessel;
use App\Models\VesselCabin;
use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoyageCabinPriceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * When creating a price, ensure cabin exists.
     *
     * @return void
     */
    public function testUserCanCreatePriceSuccessfully()
    {
        $vessel = Vessel::factory()->create(['companyId' => '1']);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $port1  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $port2  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $today  = Carbon::now();

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => '1',
            'voyageReferenceNumber' => GenerateVoyageId::execute($vessel->companyId)
        ]);

        $request = [
            'cabinId'    => $cabin->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $vessel->companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson(['data' => $request]);

        $this->assertDatabaseHas('voyage_cabin_prices', $request);
    }

    /**
     * When creating a price, ensure cabin exists.
     *
     * @return void
     */
    public function testUserCannotCreatePriceUnlessCabinExists()
    {
        $vessel = Vessel::factory()->create(['companyId' => '1']);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $port1  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $port2  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $today  = Carbon::now();

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => '1',
            'voyageReferenceNumber' => GenerateVoyageId::execute($vessel->companyId)
        ]);

        $request = [
            'cabinId'    => '99',
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $vessel->companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['cabinId'][0],
            'The selected cabin id is invalid.'
        );
    }

    /**
     * When creating a price, ensure voyage exists.
     *
     * @return void
     */
    public function testUserCannotCreatePriceUnlessVoyageExists()
    {
        $vessel = Vessel::factory()->create(['companyId' => '1']);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $port1  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $port2  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $today  = Carbon::now();

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => '1',
            'voyageReferenceNumber' => GenerateVoyageId::execute($vessel->companyId)
        ]);

        $request = [
            'cabinId'    => $cabin->id,
            'voyageId'   => '99',
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $vessel->companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['voyageId'][0],
            'The selected voyage id is invalid.'
        );
    }

    /**
     * When creating a price, ensure money is minor.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithoutCorrectPriceMinorValue()
    {
        $vessel = Vessel::factory()->create(['companyId' => '1']);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $port1  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $port2  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $today  = Carbon::now();

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => '1',
            'voyageReferenceNumber' => GenerateVoyageId::execute($vessel->companyId)
        ]);

        $request = [
            'cabinId'    => $cabin->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '110.00',
            'companyId'  => $vessel->companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['priceMinor'][0],
            'The price minor must be an integer.'
        );
    }

    /**
     * When creating a price, ensure currency is supplied.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithoutCurrency()
    {
        $vessel = Vessel::factory()->create(['companyId' => '1']);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $port1  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $port2  = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $today  = Carbon::now();

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => '1',
            'voyageReferenceNumber' => GenerateVoyageId::execute($vessel->companyId)
        ]);

        $request = [
            'cabinId'    => $cabin->id,
            'voyageId'   => $voyage->id,
            'currency'   => '',
            'priceMinor' => '11000',
            'companyId'  => $vessel->companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['currency'][0],
            'The currency field is required.'
        );
    }
}
