<?php

namespace Tests\Feature;

use App\Models\VoyageCabinPrice;
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $request = [
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $request = [
            'cabinId'    => '99',
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);

        $request = [
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => '99',
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $request = [
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '110.00',
            'companyId'  => $companyId
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $request = [
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => '',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyageInactive($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

        ]);

        $request = [
            'currency'   => 'USD',
            'priceMinor' => '99000',
            'companyId'  => $companyId
        ];

        $jsonResponse = $this->postJson('/api/prices/update/'.$price->id, $request);

        $jsonResponse->assertStatus(200)
            ->assertJson(['data' => $request]);

        $this->assertDatabaseHas('voyage_cabin_prices', $request);
    }

    /**
     * Ensure user cannot update another company's cabin price.
     *
     * @return void
     */
    public function testUserCannotUpdateOtherUsersPrices()
    {
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $cabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $cabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId
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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyageInactive($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
        $companyId = '1';
        $testDataVesselCabin = VoyageTestSetupService::createTestDataVesselCabin($companyId);
        $voyage = VoyageTestSetupService::createTestDataVoyage($companyId);

        $price = VoyageCabinPrice::create([
            'cabinId'    => $testDataVesselCabin['cabin']->id,
            'voyageId'   => $voyage->id,
            'currency'   => 'EUR',
            'priceMinor' => '11000',
            'companyId'  => $companyId

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
}
