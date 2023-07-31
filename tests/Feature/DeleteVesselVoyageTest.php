<?php

namespace Tests\Feature;

use App\Helpers\GenerateVoyageId;
use Tests\TestCase;
use App\Models\Vessel;
use App\Models\VesselCabin;
use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DeleteVesselVoyageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure user cannot delete another company's voyage.
     *
     * @return void
     */
    public function testUserCannotDeleteAnotherCompanysVoyage()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
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

        $request = [
            'companyId' => 2
        ];

        $jsonResponse = $this->postJson('/api/voyages/delete/' . $voyage['id'], $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'Voyage not found'
                     ]);

        $this->assertDatabaseHas('vessel_voyages', [
            'title'                 => $voyage['title'],
            'description'           => $voyage['description'],
            'voyageReferenceNumber' => $voyage['voyageReferenceNumber']
        ]);
    }

    /**
     * Ensure user cannot delete a voyage if it is active.
     *
     * @return void
     */
    public function testUserCannotDeleteActiveVoyage()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
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

        $request = [
            'companyId' => 1
        ];

        $jsonResponse = $this->postJson('/api/voyages/delete/' . $voyage['id'], $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'Voyage is active. Convert status to draft or cancelled.'
                     ]);

        $this->assertDatabaseHas('vessel_voyages', [
            'title'                 => $voyage['title'],
            'description'           => $voyage['description'],
            'voyageReferenceNumber' => $voyage['voyageReferenceNumber']
        ]);
    }

    /**
     * Ensure user cannot delete a voyage where the reference should be maintained.
     *
     * @return void
     */
    public function testUserCannotDeleteExpiredVoyage()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->subDays(112)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->subDays(122)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $request = [
            'companyId' => 1
        ];

        $jsonResponse = $this->postJson('/api/voyages/delete/' . $voyage['id'], $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'This voyage has expired. It cannot be deleted.'
                     ]);

        $this->assertDatabaseHas('vessel_voyages', [
            'title'                 => $voyage['title'],
            'description'           => $voyage['description'],
            'voyageReferenceNumber' => $voyage['voyageReferenceNumber']
        ]);
    }

    /**
     * Ensure user can delete a voyage if its status is set to cancelled (or draft).
     *
     * @return void
     */
    public function testUserCanDeleteCancelledVoyageSuccessfully()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
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
            'voyageStatus'          => 'CANCELLED',
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $request = [
            'companyId' => 1
        ];

        $jsonResponse = $this->postJson('/api/voyages/delete/' . $voyage['id'], $request);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('vessel_voyages', [
            'title'                 => $voyage['title'],
            'description'           => $voyage['description'],
            'voyageReferenceNumber' => $voyage['voyageReferenceNumber']
        ]);

        $this->assertDatabaseCount('vessel_voyages', 0);
    }

    /**
     * If user deletes a voyage, ensure all its prices are also deleted
     * - as well as associated pivot table records.
     *
     * @return void
     */
    public function testDeletedVoyagesPricesAreRemoved()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $cabin1        = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $cabin2        = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $cabin3        = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
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
            'voyageStatus'          => 'CANCELLED',
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [1, 2, 3],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson(
            '/api/prices/create', $createPriceRequest
        );

        $createPriceResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment(['title'      => $createPriceRequest['title']])
                     ->assertJsonFragment(['voyageId'   => $createPriceRequest['voyageId']])
                     ->assertJsonFragment(['priceMinor' => $createPriceRequest['priceMinor']]);

        $this->assertDatabaseHas('voyage_prices', [
            'title'      => $createPriceRequest['title'],
            'voyageId'   => $createPriceRequest['voyageId'],
            'priceMinor' => $createPriceRequest['priceMinor']
        ]);

        $this->assertDatabaseHas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin1->id,

            'id'      => 2,
            'priceId' => 1,
            'cabinId' => $cabin2->id,

            'id'      => 3,
            'priceId' => 1,
            'cabinId' => $cabin3->id,
        ]);

        $deleteVoyageRequest = [
            'companyId' => 1
        ];

        $deleteVoyageResponse = $this->postJson(
            '/api/voyages/delete/' . $voyage['id'], $deleteVoyageRequest
        );

        $deleteVoyageResponse->assertStatus(200)
                             ->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $createPriceRequest['title'],
            'voyageId'   => $createPriceRequest['voyageId'],
            'priceMinor' => $createPriceRequest['priceMinor']
        ]);

        $this->assertDatabaseCount('voyage_prices', 0);

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin1->id,

            'id'      => 2,
            'priceId' => 1,
            'cabinId' => $cabin2->id,

            'id'      => 3,
            'priceId' => 1,
            'cabinId' => $cabin3->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', 0);
    }
}
