<?php

namespace Tests\Feature;

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
}
