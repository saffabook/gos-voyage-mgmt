<?php

namespace Tests\Feature;

use App\Helpers\GenerateVoyageId;
use App\Models\Vessel;
use App\Models\VesselCabin;
use App\Models\VesselVoyage;
use App\Models\VoyageCabinPrice;
use App\Models\VoyagePort;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\CommonSetups\Voyages\VoyageTestSetupService;

class VoyageCabinPriceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirm cabin price can be created successfully.
     *
     * @return void
     */
    public function testUserCanCreatePriceSuccessfully()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

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
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => '99',
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson(['error' => 'Cabin not found']);
    }

    /**
     * When creating a price, ensure voyage exists.
     *
     * @return void
     */
    public function testUserCannotCreatePriceUnlessVoyageExists()
    {
        $companyId = 1;
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $testDataVesselCabin['cabin']->id,
            'voyageId'    => '99',
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson(['error' => 'Voyage not found']);
    }

    /**
     * When creating a price, ensure money is minor.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithoutCorrectPriceMinorValue()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '110.00',
            'companyId'   => $companyId
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
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => '',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['currency'][0],
            'The currency field is required.'
        );
    }

    /**
     * When updating a cabin price, ensure modifying inactive voyage yields no errors.
     *
     * @return void
     */
    public function testUserCanUpdateTheirOwnPriceWhenVoyageIsNotActive()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyageInactive($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId

        ]);

        $request = [
            'currency'   => 'USD',
            'priceMinor' => '99000',
            'companyId'  => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(200)->assertJson(['data' => $request]);

        $this->assertDatabaseHas('voyage_cabin_prices', $request);
    }

    /**
     * Ensure user cannot update another company's cabin price.
     *
     * @return void
     */
    public function testUserCannotUpdateOtherUsersPrices()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'currency'   => 'EUR',
            'priceMinor' => '99999',
            'companyId'  => '99'
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Price not found.'
            ]);
    }

    /**
     * Ensure user cannot update cabin price if voyage is active.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceIfVoyageIsActive()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'currency'   => 'EUR',
            'priceMinor' => '99999',
            'companyId'  => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Voyage is active. Do you want to add a discount price?'
            ]);

        $this->assertDatabaseMissing('voyage_cabin_prices', $request);
    }

    /**
     * Ensure user can update cabin price with confirmation, if voyage is active.
     *
     * @return void
     */
    public function testUserCanUpdatePriceWithForceAction()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'currency'    => 'EUR',
            'priceMinor'  => '99999',
            'companyId'   => $companyId,
            'forceAction' => true
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(200)
            ->assertJsonPath('data.message', 'The price has been updated.');

        $this->assertDatabaseHas('voyage_cabin_prices', [
            'id'         => $price->id,
            'currency'   => $request['currency'],
            'priceMinor' => $request['priceMinor']
        ]);
    }

    /**
     * Ensure price is minor.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceIfValueIsNotMinor()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'currency'    => 'EUR',
            'priceMinor'  => '999.99',
            'companyId'   => $companyId,
            'forceAction' => true
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['priceMinor'][0],
            'The price minor must be an integer.'
        );

        $this->assertDatabaseMissing('voyage_cabin_prices', [
            'priceMinor' => $request['priceMinor']
        ]);
    }

    /**
     * Ensure currency is present.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceWithoutCurrency()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'priceMinor'  => '99999',
            'companyId'   => $companyId,
            'forceAction' => true
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['currency'][0],
            'The currency field is required.'
        );

        $this->assertDatabaseMissing('voyage_cabin_prices', $request);
    }

    /**
     * Ensure discount price is not set higher than price.
     *
     * @return void
     */
    public function testUserCannotUpdatePriceIfDiscountIsGreaterThanOriginal()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'currency'             => 'EUR',
            'discountedPriceMinor' => '99999',
            'companyId'            => $companyId,
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Discounted price must be less than original price.'
            ]);

        $this->assertDatabaseMissing('voyage_cabin_prices', [
            'discountedPriceMinor' => $request['discountedPriceMinor']
        ]);
    }

    /**
     * If changing price without force action, respond with
     * suggesting to add a discount price.
     *
     * @return void
     */
    public function testUserMessageToAddDiscountPriceIfForceActionIsNotPresent()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'currency'   => 'EUR',
            'priceMinor' => '99999',
            'companyId'  => $companyId,
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Voyage is active. Do you want to add a discount price?'
            ]);

        $this->assertDatabaseMissing('voyage_cabin_prices', $request);
    }

    /**
     * Ensure the user can only delete their own prices.
     *
     * @return void
     */
    public function testUserCannotDeleteOtherUsersPrices()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'companyId' => '2'
        ];

        $jsonResponse = $this->postJson('/api/prices/delete/'.$price->id, $request);

        $jsonResponse->assertStatus(422)
            ->assertJson(['error' => 'Price not found.']);

        $this->assertDatabaseHas('voyage_cabin_prices', ['id' => $price->id]);
    }

    /**
     * Ensure the user can delete price successfully when voyage is not active.
     *
     * @return void
     */
    public function testUserCanDeleteTheirOwnPriceWhenVoyageIsNotActive()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyageInactive($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'companyId' => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/delete/'.$price->id, $request);

        $jsonResponse->assertStatus(200)
            ->assertJsonPath('data.message', 'Price deleted successfully');

        $this->assertDatabaseMissing('voyage_cabin_prices', ['id' => $price->id]);
    }

    /**
     * If the user attempts to delete a cabin price when the voyage is active,
     * ensure the response throws an error message suggesting to convert the
     * voyage to draft or cancelled.
     *
     * @return void
     */
    public function testUserCannotDeletePriceIfVoyageIsActive()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $price = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'companyId' => $companyId,
        ];

        $jsonResponse = $this->postJson('/api/prices/delete/'.$price->id, $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Voyage is active. Convert status to draft or cancelled.'
            ]);

        $this->assertDatabaseHas('voyage_cabin_prices', ['id' => $price->id]);
    }

    /**
     * When creating a price, ensure more than one price can be created for a cabin.
     *
     * @return void
     */
    public function testUserCanCreateMoreThanOnePriceForACabin()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $cabinPriceInDb = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'title'       => 'children',
            'description' => 'price for children',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson(['data' => $request]);

        $this->assertDatabaseHas('voyage_cabin_prices', [
            'title' => $cabinPriceInDb->title,
            'title' => $request['title']
        ]);
    }

    /**
     * When creating a price, ensure there is a title for the cabin price.
     *
     * @return void
     */
    public function testUserCannotCreatePriceWithoutTitle()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $request = [
            'description' => 'price for PHP developers',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);
        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['title'][0],
            'The title field is required.'
        );

        $this->assertDatabaseMissing('voyage_cabin_prices', $request);
    }

    /**
     * Ensure the user cannot create prices with identical titles for the same voyage.
     *
     * @return void
     */
    public function testUserCannotCreateDuplicatePriceTitleForCabinPerVoyage()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $cabinPriceInDb = VoyageCabinPrice::create([
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)->assertJson([
            'error' => "The title 'adults' already exists'. Please create a different title."
        ]);

        $this->assertDatabaseHas('voyage_cabin_prices', [
            'title' => $cabinPriceInDb->title
        ])->assertDatabaseCount('voyage_cabin_prices', 1);
    }

    /**
     * Ensure the user can create prices on different voyages with identical titles.
     *
     * @return void
     */
    public function testUserCanCreateSamePriceTitleForCabinOnSeparateVoyages()
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
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage1->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request1);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson(['data' => $request1]);

        $request2 = [
            'title'       => 'adults',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage2->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request2);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson(['data' => $request2]);

        $this->assertDatabaseHas('voyage_cabin_prices', [
            'title' => $request1['title'],
            'title' => $request2['title']
        ]);
    }

    /**
     * Ensure the user cannot create prices with similar titles for the same voyage.
     *
     * @return void
     */
    public function testUserCannotCreateSimilarPriceTitlesForCabin()
    {
        $companyId = 1;
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);
        $cabin  = VesselCabin::factory()->create(['vessel_id' => $voyage['vesselId']]);

        $cabinPriceInDb = VoyageCabinPrice::create([
            'title'       => 'child',
            'description' => 'price for adult',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '11000',
            'companyId'   => $companyId
        ]);

        $request = [
            'title'       => 'children',
            'description' => 'price for adults',
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)->assertJson([
            'error' => "The title 'children' is too similar to 'child'. Please create a different title."
        ]);

        $this->assertDatabasehas('voyage_cabin_prices', [
            'title' => $cabinPriceInDb->title
        ]);

        $this->assertDatabaseMissing('voyage_cabin_prices', [
            'title' => $request['title']
        ]);
    }

    /**
     * Ensure the user cannot create prices for cabins that are part of a
     * voyage that has already ended.
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
            'cabinId'     => $cabin->id,
            'voyageId'    => $voyage->id,
            'currency'    => 'EUR',
            'priceMinor'  => '9900',
            'companyId'   => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/create', $request);

        $jsonResponse->assertStatus(422)->assertJson([
            'error' => "The voyage 'Test Voyage' has expired."
        ]);

        $this->assertDatabaseMissing('voyage_cabin_prices', $request);
    }
}
