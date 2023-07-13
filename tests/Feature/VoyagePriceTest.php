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

class VoyagePriceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirm cabin price can be created successfully.
     *
     * @return void
     */
    public function testUserCanCreatePriceSuccessfully()
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

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment(['title'      => $request['title']])
                     ->assertJsonFragment(['voyageId'   => $request['voyageId']])
                     ->assertJsonFragment(['priceMinor' => $request['priceMinor']]);

        $this->assertDatabasehas('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['priceMinor']
        ]);

        $this->assertDatabasehas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id
        ]);
    }


    /**
     * Ensure cabins can have more than one price.
     *
     * @return void
     */
    public function testUserCanCreatePriceToAttachToMultipleCabins()
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
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            // 'cabinIds'    => [$cabin1->id, $cabin2->id, $cabin3->id],
            'cabinIds'    => [1, 2, 3],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment(['title'      => $request['title']])
                     ->assertJsonFragment(['voyageId'   => $request['voyageId']])
                     ->assertJsonFragment(['priceMinor' => $request['priceMinor']]);

        $this->assertDatabasehas('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['priceMinor']
        ]);

        $this->assertDatabasehas('price_cabin_pivot', [
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
    }

    /**
     * Ensure more than one price can be created for a cabin.
     *
     * @return void
     */
    public function testUserCanCreateMultiplePricesForSameCabin()
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

        $firstPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $initialResponse = $this->postJson('/api/prices/create', $firstPriceRequest);
        $initialResponse->assertStatus(200);

        $secondPriceRequest = [
            'title'       => 'children',
            'description' => 'price for children',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 5500,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $secondPriceRequest);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment(['title'      => $secondPriceRequest['title']])
                     ->assertJsonFragment(['voyageId'   => $secondPriceRequest['voyageId']])
                     ->assertJsonFragment(['priceMinor' => $secondPriceRequest['priceMinor']]);

        $this->assertDatabasehas('voyage_prices', [
            'title'      => $firstPriceRequest['title'],
            'voyageId'   => $firstPriceRequest['voyageId'],
            'priceMinor' => $firstPriceRequest['priceMinor']
        ]);

        $this->assertDatabasehas('voyage_prices', [
            'title'      => $secondPriceRequest['title'],
            'voyageId'   => $secondPriceRequest['voyageId'],
            'priceMinor' => $secondPriceRequest['priceMinor']
        ]);

        $this->assertDatabasehas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,

            'id'      => 2,
            'priceId' => 2,
            'cabinId' => $cabin->id,
        ]);
    }


    /**
     * Ensure cabin(s) exist(s).
     *
     * @return void
     */
    public function testUserCannotCreatePriceToAttachToCabinThatDoesNotExist()
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
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [99],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'Cabin id 99 does not belong to the selected vessel. You cannot add a price to this cabin'
                     ]);

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['priceMinor']
        ]);

        $this->assertDatabaseCount('voyage_prices', '0');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => 99,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * Ensure voyage exists.
     *
     * @return void
     */
    public function testUserCannotCreatePriceToAttachToCabinForVoyageThatDoesNotExist()
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

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => 99,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'Voyage not found'
                     ]);

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['priceMinor']
        ]);

        $this->assertDatabaseCount('voyage_prices', '0');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * Ensure request cabin ids is an array of integers.
     *
     * @return void
     */
    public function testUserCannotCreatePriceToAttachToCabinsWhereIdsAreInvalid()
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

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => ['one'],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['cabinIds.0'][0], 'The cabinIds.0 must be an integer.'
        );

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['priceMinor']
        ]);

        $this->assertDatabaseCount('voyage_prices', '0');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $request['cabinIds'][0],
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * When creating a price, ensure priceMinor value is an integer.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithInvalidPriceValue()
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

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            // 'priceMinor'  => 110.00,
            'priceMinor'  => 110.99,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        // var_dump($jsonResponse);
        // exit;

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['priceMinor'][0],
            'The price minor must be an integer.'
        );

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['priceMinor']
        ]);

        $this->assertDatabaseCount('voyage_prices', '0');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * When creating a price, ensure currency is supplied.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithoutCurrency()
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

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => '',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['currency'][0],
            'The currency field is required.'
        );

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['currency']
        ]);

        $this->assertDatabaseCount('voyage_prices', '0');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * When creating a price, ensure there is a title for the cabin price.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithoutTitle()
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

        $request = [
            'title'       => '',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['title'][0],
            'The title field is required.'
        );

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $request['title'],
            'voyageId'   => $request['voyageId'],
            'priceMinor' => $request['currency']
        ]);

        $this->assertDatabaseCount('voyage_prices', '0');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * Ensure prices with identical titles cannot be created for the same voyage.
     *
     * @return void
     */
    public function testUserCannotCreateDuplicatePriceTitleForCabinPerVoyage()
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

        $initialRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $initialResponse = $this->postJson('/api/prices/create', $initialRequest);
        $initialResponse->assertStatus(200);

        $duplicateRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $duplicateResponse = $this->postJson('/api/prices/create', $duplicateRequest);
        $duplicateResponse->assertStatus(422)->assertJson([
            "error" => [
                "errorType" => "Price title match",
                "message"   => "The cabin '{$cabin->title}' already has a price for this voyage called '{$initialRequest['title']}', which is too similar to '{$duplicateRequest['title']}'. Please create a different title."
            ]
        ]);

        $this->assertDatabasehas('voyage_prices', [
            'title'      => $initialRequest['title'],
            'voyageId'   => $initialRequest['voyageId'],
            'priceMinor' => $initialRequest['priceMinor']
        ]);

        $this->assertDatabasehas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id
        ]);

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $duplicateRequest['title'],
            'voyageId'   => $duplicateRequest['voyageId'],
            'priceMinor' => $duplicateRequest['currency']
        ]);

        $this->assertDatabaseCount('voyage_prices', '1');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 2,
            'priceId' => 2,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '1');
    }


    /**
     * Ensure prices cannot be created with similar titles for the same voyage.
     *
     * @return void
     */
    public function testUserCannotCreateTooSimilarPriceTitleForCabinPerVoyage()
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

        $initialRequest = [
            'title'       => 'child',
            'description' => 'price for children',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $initialResponse = $this->postJson('/api/prices/create', $initialRequest);
        $initialResponse->assertStatus(200);

        $similarTitleRequest = [
            'title'       => 'children',
            'description' => 'price for children',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $similarTitleResponse = $this->postJson('/api/prices/create', $similarTitleRequest);
        $similarTitleResponse->assertStatus(422)->assertJson([
            "error" => [
                "errorType" => "Price title match",
                "message"   => "The cabin '{$cabin->title}' already has a price for this voyage called 'child', which is too similar to 'children'. Please create a different title."
            ]
        ]);

        $this->assertDatabasehas('voyage_prices', [
            'title'      => $initialRequest['title'],
            'voyageId'   => $initialRequest['voyageId'],
            'priceMinor' => $initialRequest['priceMinor']
        ]);

        $this->assertDatabasehas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id
        ]);

        $this->assertDatabaseMissing('voyage_prices', [
            'title'      => $similarTitleRequest['title'],
            'voyageId'   => $similarTitleRequest['voyageId'],
            'priceMinor' => $similarTitleRequest['currency']
        ]);

        $this->assertDatabaseCount('voyage_prices', '1');

        $this->assertDatabaseMissing('price_cabin_pivot', [
            'id'      => 2,
            'priceId' => 2,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('price_cabin_pivot', '1');
    }


    /**
     * Ensure prices cannot be created for a voyage that has already been completed.
     *
     * @return void
     */
    public function testUserCannotCreateCabinPriceIfVoyageHasExpired()
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
            'startDate'             => Carbon::now()->subDays(22)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->subDays(12)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $request = [
            'title'       => 'test',
            'description' => 'price for testing',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)->assertJson([
            'error' => "The voyage 'Test Voyage' has expired."
        ]);

        $this->assertDatabaseMissing('voyage_prices', $request);
        $this->assertDatabaseCount('voyage_prices', '0');
        $this->assertDatabaseCount('price_cabin_pivot', '0');
    }


    /**
     * Ensure prices with identical titles can be created for same cabin on different voyages.
     *
     * @return void
     */
    public function testUserCanCreateIdenticalPricesForCabinOnSeparateVoyages()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $cabin         = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage1 = VesselVoyage::create([
            'title'                 => 'Test Voyage One',
            'description'           => 'Description for Test Voyage One',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->subDays(2)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(2)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $voyage2 = VesselVoyage::create([
            'title'                 => 'Test Voyage Two',
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

        $request1 = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage1->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request1);
        $jsonResponse->assertStatus(200);

        $request2 = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage2->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request2);
        $jsonResponse->assertStatus(200);

        $this->assertDatabaseHas('voyage_prices', [
            'title' => $request1['title'],
            'title' => $request2['title']
        ]);

        $this->assertDatabaseHas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,

            'id'      => 2,
            'priceId' => 2,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('voyage_prices', '2');
        $this->assertDatabaseCount('price_cabin_pivot', '2');
    }

    /**
     * When updating a price, ensure modifying inactive voyage yields no errors.
     *
     * @return void
     */
    public function testUserCanUpdateTheirOwnPriceWhenVoyageIsNotActive()
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
            'voyageStatus'          => 'DRAFT',
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'title'       => 'updated title',
            'description' => 'updated description',
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 42000,
            'companyId'   => $companyId
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(200)
                            ->assertJsonStructure(['data'])
                            ->assertJsonFragment(['title'       => $updatePriceRequest['title']])
                            ->assertJsonFragment(['description' => $updatePriceRequest['description']])
                            ->assertJsonFragment(['priceMinor'  => $updatePriceRequest['priceMinor']]);

        $this->assertDatabasehas('voyage_prices', [
            'title'       => $updatePriceRequest['title'],
            'description' => $updatePriceRequest['description'],
            'priceMinor'  => $updatePriceRequest['priceMinor']
        ]);
    }

    /**
     * Ensure user cannot update another company's price.
     *
     * @return void
     */
    public function testUserCannotUpdateOtherUsersPrices()
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
            'voyageStatus'          => 'DRAFT',
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'title'       => 'updated title',
            'description' => 'updated description',
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 42000,
            'companyId'   => 2
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(422)
                            ->assertJson([
                                'error' => 'Price not found.'
                            ]);
    }

    /**
     * Ensure user cannot update price's priceMinor value if voyage is active.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceMinorValueIfVoyageIsActive()
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

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'voyageId'    => $voyage->id,
            'priceMinor'  => 42000,
            'companyId'   => $companyId
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(422)
                            ->assertJson([
                                'error' => 'This voyage is active. Please confirm you would like to change this. Would you like to add a promotional price?'
                            ]);
    }

    /**
     * Ensure user can update price with confirmation, if voyage is active.
     *
     * @return void
     */
    public function testUserCanUpdatePriceWithForceAction()
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

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'voyageId'    => $voyage->id,
            'priceMinor'  => 42000,
            'companyId'   => $companyId,
            'forceAction' => 1
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(200)
                            ->assertJsonStructure(['data'])
                            ->assertJsonFragment(['priceMinor' => $updatePriceRequest['priceMinor']]);

        $this->assertDatabasehas('voyage_prices', [
            'title'       => $createPriceRequest['title'],
            'description' => $createPriceRequest['description'],
            'priceMinor'  => $updatePriceRequest['priceMinor']
        ]);
    }

    /**
     * Ensure updated priceMinor is an integer.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceIfValueIsNotMinor()
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

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'voyageId'    => $voyage->id,
            // 'priceMinor'  => 420.00,
            'priceMinor'  => 420.99,
            'companyId'   => $companyId,
            'forceAction' => 1
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(422);

        $this->assertSame(
            $updatePriceResponse['error']['priceMinor'][0],
            'The price minor must be an integer.'
        );

        $this->assertDatabaseMissing('voyage_prices', [
            'priceMinor' => $updatePriceRequest['priceMinor']
        ]);
    }

    // /**
    //  * Ensure currency is present.
    //  *
    //  * @return void
    //  */
    // public function testUserCannotUpdatePriceWithoutCurrency()
    // {}

    /**
     * Ensure discountedPriceMinor is not set higher than priceMinor.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceIfDiscountValueIsGreaterThanOriginal()
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

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'voyageId'             => $voyage->id,
            'discountedPriceMinor' => 42000,
            'companyId'            => $companyId
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(422)
                            ->assertJson([
                                'error' => 'Discounted price must be lower than original price.'
                            ]);

        $this->assertDatabaseMissing('voyage_prices', [
            'discountedPriceMinor' => $updatePriceRequest['discountedPriceMinor']
        ]);
    }

    /**
     * Ensure updated price title cannot be identical to a price title attached
     * to the same cabin for the same voyage.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceTitleToBeDuplicateForCabinPerVoyage()
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

        $createPriceRequest = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPriceResponse = $this->postJson('/api/prices/create', $createPriceRequest);
        $createPriceResponse->assertStatus(200);

        $price = $createPriceResponse['data'];

        $updatePriceRequest = [
            'voyageId'  => $voyage->id,
            'title'     => 'adults',
            'companyId' => $companyId
        ];

        $updatePriceResponse = $this->postJson('/api/prices/update/' . $price['id'], $updatePriceRequest);

        $updatePriceResponse->assertStatus(422)->assertJson([
            "error" => [
                "errorType" => "Price title match",
                "message"   => "The cabin '{$cabin->title}' already has a price for this voyage called '{$createPriceRequest['title']}', which is too similar to '{$updatePriceRequest['title']}'. Please create a different title."
            ]
        ]);
    }

    /**
     * Ensure updated price title can be identical to a price title attached to
     * the same cabin on different voyages.
     *
     * @return void
     */
    public function testUserCanUpdatePriceTitleToBeDuplicateForCabinOnSeparateVoyages()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $cabin         = VesselCabin::factory()->create(['vessel_id' => $vessel->id]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage1 = VesselVoyage::create([
            'title'                 => 'Test Voyage One',
            'description'           => 'Description for Test Voyage One',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->subDays(2)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(2)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);

        $voyage2 = VesselVoyage::create([
            'title'                 => 'Test Voyage Two',
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

        $voyage1Price = [
            'title'       => 'test',
            'description' => 'price for testing',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage1->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPrice1Response = $this->postJson('/api/prices/create', $voyage1Price);
        $createPrice1Response->assertStatus(200);

        $voyage2Price = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinIds'    => [$cabin->id],
            'voyageId'    => $voyage2->id,
            'currency'    => 'EUR',
            'priceMinor'  => 11000,
            'companyId'   => $companyId
        ];

        $createPrice2Response = $this->postJson('/api/prices/create', $voyage2Price);
        $createPrice2Response->assertStatus(200);

        $this->assertDatabaseHas('voyage_prices', [
            'title' => $voyage1Price['title'],
            'title' => $voyage2Price['title']
        ]);

        $this->assertDatabaseHas('price_cabin_pivot', [
            'id'      => 1,
            'priceId' => 1,
            'cabinId' => $cabin->id,

            'id'      => 2,
            'priceId' => 2,
            'cabinId' => $cabin->id,
        ]);

        $this->assertDatabaseCount('voyage_prices', '2');
        $this->assertDatabaseCount('price_cabin_pivot', '2');


        $price1 = $createPrice1Response['data'];

        $updatePrice1Request = [
            'title'       => 'adults',
            'description' => 'updated description',
            'voyageId'    => $voyage1->id,
            'companyId'   => $companyId
        ];

        $updatePrice1Response = $this->postJson('/api/prices/update/' . $price1['id'], $updatePrice1Request);

        $updatePrice1Response->assertStatus(200)
                             ->assertJsonStructure(['data'])
                             ->assertJsonFragment(['title' => $updatePrice1Request['title']]);

        $this->assertDatabasehas('voyage_prices', [
            'title'       => $updatePrice1Request['title'],
            'description' => $updatePrice1Request['description'],
            'priceMinor'  => $price1['priceMinor']
        ]);
    }
}
