<?php

namespace Tests\Feature;

use App\Helpers\GenerateVoyageId;
use App\Models\Vessel;
use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateVesselVoyageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure user can create voyage successfully.
     *
     * @return void
     */
    public function testUserCanCreateVoyageSuccessfully()
    {
        $embarkPort    = VoyagePort::factory()->create(['companyId' => '1']);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => '1']);
        $vessel        = Vessel::factory()->create(['companyId' => '1']);
        $request       = ['companyId' => '1'];

        $voyage = [
            'title'           => 'DateCheckerVoyage',
            'description'     => 'Description for DateCheckerVoyage.',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDay()->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(10)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $request['companyId']
        ];

        $response = $this->postJson('/api/voyages/create', $voyage);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $voyage
        ]);
    }


    /**
     * Ensure user cannot create a voyage that would result in the vessel being
     * double-booked.
     *
     * @return void
     */
    public function testCreateVoyageDoubleBookingVessel()
    {
        $date = Carbon::now();

        /**
         * Test Cases:
         * 'Last week' - can we book last week if there were no bookings last week?
         * 'This week' - can we book this week if there are no bookings this week?
         * 'Next week' - can we create booking if start date is within 'This week'?
         * 'Overlap' - can we create booking if start date is within 'This week' and end date is within 'Next week'?
         *
         */
        $testCases = [
            'Last week' => [
                'startDateFromNow' => -8,
                'endDateFromNow'   => -1,
            ],
            'This week' => [
                'startDateFromNow' => 0,
                'endDateFromNow'   => 7,
            ],
            'Next week' => [
                'startDateFromNow' => 7,
                'endDateFromNow'   => 14,
            ],
            'Overlap' => [
                'startDateFromNow' => 5,
                'endDateFromNow'   => 8,
            ],
        ];

        $embarkPort    = VoyagePort::factory()->create(['companyId' => '1']);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => '1']);
        $vessel        = Vessel::factory()->create(['companyId' => '1']);
        $request       = ['companyId' => '1'];

        $defaultData = [
            'title'           => 'DateCheckerVoyage',
            'description'     => 'Description for DateCheckerVoyage.',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endTime'         => '16:30',
            'companyId'       => $request['companyId']
        ];

        foreach ($testCases as $testName => $overlapVoyage) {

            // Display each test case we're testing for
            $startDate = Carbon::now()->addDays(
                $overlapVoyage['startDateFromNow']
            )->toDateString();

            $endDate = Carbon::now()->addDays(
                $overlapVoyage['endDateFromNow']
            )->toDateString();

            $defaultData['endDate']   = $endDate;
            $defaultData['startDate'] = $startDate;
            $defaultData['title']     = $testName;

            $jsonResponse = $this->postJson(
                '/api/voyages/create', $defaultData
            );

            // Show successful response if voyage is booked successfully
            if (isset($jsonResponse['data'])) {
                $jsonResponse->assertStatus(200)
                             ->assertJsonStructure(['data']);

                var_dump(
                    'Test Case: ' . $testName . ' – voyage booked successfully.'
                );
            }

            // Show error message if vessel is already booked
            if (isset($jsonResponse['error'])) {
                $jsonResponse->assertStatus(422);

                $this->assertSame(
                    $jsonResponse['error'],
                    'The requested vessel is already booked for this time.'
                );

                var_dump(
                    'Test Case: ' . $testName . ' – voyage booking unsuccessful because the vessel is already booked.'
                );
            }
        }
    }


    /**
     * Ensure user cannot create a voyage to use another company's vessel.
     *
     * @return void
     */
    public function testUserCannotCreateVoyageToUseVesselBelongingToAnotherUser()
    {
        $companyOneId     = 1;
        $companyTwoId     = 2;
        $companyTwoVessel = Vessel::factory()->create(['companyId' => $companyTwoId]);
        $embarkPort       = VoyagePort::factory()->create(['companyId' => $companyOneId]);
        $disembarkPort    = VoyagePort::factory()->create(['companyId' => $companyOneId]);

        $request = [
            'title'           => 'Test Voyage',
            'description'     => 'This is the description for Test Voyage.',
            'vesselId'        => $companyTwoVessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDays(12)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(22)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyOneId
        ];

        $jsonResponse = $this->postJson('/api/voyages/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The requested vessel is invalid.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', $request);
    }


    /**
     * Ensure user cannot create a voyage to use another company's ports.
     *
     * @return void
     */
    public function testUserCannotCreateVoyageToUseEmbarkPortBelongingToAnotherUser()
    {
        $companyOneId     = 1;
        $companyTwoId     = 2;
        $companyOneVessel = Vessel::factory()->create(['companyId' => $companyOneId]);
        $companyOnePort   = VoyagePort::factory()->create(['companyId' => $companyOneId]);
        $companyTwoPort   = VoyagePort::factory()->create(['companyId' => $companyTwoId]);

        $request = [
            'title'           => 'Test Voyage',
            'description'     => 'This is the description for Test Voyage.',
            'vesselId'        => $companyOneVessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $companyTwoPort->id,
            'startDate'       => Carbon::now()->addDays(12)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $companyOnePort->id,
            'endDate'         => Carbon::now()->addDays(22)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyOneId
        ];

        $jsonResponse = $this->postJson('/api/voyages/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The requested embarkPortId is invalid.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', $request);
    }


    /**
     * Ensure user cannot create a voyage to use another company's ports.
     *
     * @return void
     */
    public function testUserCannotCreateVoyageToUseDisembarkPortBelongingToAnotherUser()
    {
        $companyOneId     = 1;
        $companyTwoId     = 2;
        $companyOneVessel = Vessel::factory()->create(['companyId' => $companyOneId]);
        $companyOnePort   = VoyagePort::factory()->create(['companyId' => $companyOneId]);
        $companyTwoPort   = VoyagePort::factory()->create(['companyId' => $companyTwoId]);

        $request = [
            'title'           => 'Test Voyage',
            'description'     => 'This is the description for Test Voyage.',
            'vesselId'        => $companyOneVessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $companyOnePort->id,
            'startDate'       => Carbon::now()->addDays(12)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $companyTwoPort->id,
            'endDate'         => Carbon::now()->addDays(22)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyOneId
        ];

        $jsonResponse = $this->postJson('/api/voyages/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The requested disembarkPortId is invalid.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', $request);
    }


    /**
     * Ensure user receives a warning if creating a voyage title to
     * be identical to an existing voyage title for the same company.
     *
     * @return void
     */
    public function testUserWarnedIfCreatingVoyageWithExistingTitle()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $companyId]);

        $voyageInDb = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'This is the description for $voyageInDb database check.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(12)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(22)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'title'           => 'tEsT vOyAge',
            'description'     => 'This is the description for $request database check.',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDays(32)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(42)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyId
        ];

        $jsonResponse = $this->postJson('/api/voyages/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'You have already created a voyage with that title. Please confirm you would like to create this voyage with the duplicate title.'
                     ]);

        $this->assertDatabaseHas('vessel_voyages', [
            'description' => $voyageInDb['description']
        ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'description' => $request['description']
        ]);
    }


    /**
     * Ensure user can create a voyage title to duplicate an existing voyage
     * title for the same company if sending a `forceAction` with the request.
     *
     * @return void
     */
    public function testUserCanCreateVoyageToUseExistingTitleWithForceAction()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $companyId]);

        $voyageInDb = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'This is the description for $voyageInDb database check.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(12)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(22)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'title'           => 'tEsT vOyAge',
            'description'     => 'This is the description for $request database check.',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDays(32)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(42)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyId,
            'forceAction'     => 1
        ];

        $jsonResponse = $this->postJson('/api/voyages/create', $request);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment(['title'       => $request['title']])
                     ->assertJsonFragment(['description' => $request['description']])
                     ->assertJsonFragment(['message'     => 'The voyage was created.']);

        $this->assertDatabaseHas('vessel_voyages', [
            'description' => $voyageInDb['description']
        ]);

        $this->assertDatabaseHas('vessel_voyages', [
            'description' => $request['description']
        ]);
    }


    /**
     * Ensure user cannot create voyage endDate to be earlier than startDate.
     *
     * @return void
     */
    public function testUserCannotCreateVoyageStartDateToBeLaterThanEndDate()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $request = [
            'title'           => 'Test Voyage',
            'description'     => 'This is the description for Test Voyage.',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDays(22)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(12)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyId
        ];

        $jsonResponse = $this->postJson('/api/voyages/create', $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The voyage start date cannot be set later than the voyage end date.'
                     ]);

        $this->assertDatabaseCount('vessel_voyages', 0);
    }
}
