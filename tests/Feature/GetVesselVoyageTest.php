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

class GetVesselVoyageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure user gets relevant voyage data from the database.
     *
     * @return void
     */
    public function testUserCanRetrieveCorrectVoyageData()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $cabin         = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(12)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(22)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $price = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPrice = $this->postJson('/api/prices/create', $price);
        $createPrice->assertStatus(200);

        $jsonResponse = $this->postJson('/api/voyages/get/' . $voyage->voyageReferenceNumber);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     // Assert response contains data relevant to the voyage.
                     ->assertJsonFragment(['id' => $voyage->id])
                     ->assertJsonFragment(['title' => $voyage->title])
                     ->assertJsonPath('data.vessel.id', $vessel->id)
                     ->assertJsonPath('data.vessel.cabins.0.id', $cabin->id)
                     ->assertJsonPath('data.embarkPort.title', $embarkPort->title)
                     ->assertJsonPath('data.disembarkPort.title', $disembarkPort->title)
                     ->assertJsonPath('data.prices.0.title', $price['title'])
                     ->assertJsonPath('data.prices.0.voyageId', $voyage->id)
                     ->assertJsonPath('data.prices.0.priceMinor', $price['priceMinor'])
                     ->assertJsonPath('data.prices.0.cabins.0.id', $cabin->id);
    }

    /**
     * Ensure user does not get data related to other voyages
     *
     * @return void
     */
    public function testUserCannotRetrieveIncorrectVoyageData()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $cabin         = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyageOne = VesselVoyage::create([
            'title'                 => 'Voyage One',
            'description'           => 'Description for Test Voyage Two',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(12)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(22)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $voyageTwo = VesselVoyage::create([
            'title'                 => 'Voyage Two',
            'description'           => 'Description for Test Voyage Two',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(42)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(44)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $priceOne = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyageOne->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $priceTwo = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyageTwo->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceOne = $this->postJson('/api/prices/create', $priceOne);
        $createPriceOne->assertStatus(200);

        $createPriceTwo = $this->postJson('/api/prices/create', $priceTwo);
        $createPriceTwo->assertStatus(200);

        $jsonResponse = $this->postJson('/api/voyages/get/' . $voyageOne->voyageReferenceNumber);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])

                     // Assert response contains data relevant to Voyage One.
                     ->assertJsonFragment(['id' => $voyageOne->id])
                     ->assertJsonFragment(['title' => $voyageOne->title])

                     // Assert response does not contain data relevant to Voyage Two.
                     ->assertJsonMissing([
                        'data' => [
                            '*' => [
                                $voyageTwo->id,
                                $voyageTwo->title,
                                $priceTwo['voyageId']
                            ]
                        ]
                     ]);
    }
}
