<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vessel;
use App\Models\VoyagePort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CreateVesselVoyageTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateVoyageSuccessful()
    {
        $embarkPort    = VoyagePort::factory()->create(['companyId' => '1']);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => '1']);
        $vessel        = Vessel::factory()->create(['companyId' => '1']);
        $date          = Carbon::now();
        $request       = ['companyId' => '1'];

        $voyage = [
            'title'           => 'DateCheckerVoyage',
            'description'     => 'Description for DateCheckerVoyage.',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => $date->addDay()->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => $date->addDays(10)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $request['companyId']
        ];

        $response = $this->postJson('/api/voyages/create', $voyage);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $voyage
        ]);
    }

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
            // var_dump('Testing for ' . $testName);

            $date = Carbon::now();

            $startDate = $date->addDays($overlapVoyage['startDateFromNow'])
                              ->toDateString();

            $endDate = $date->addDays($overlapVoyage['endDateFromNow'])
                            ->toDateString();

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
                    'The vessel is already booked for this time'
                );

                var_dump(
                    'Test Case: ' . $testName . ' – voyage booking unsuccessful because the vessel is already booked.'
                );
            }
        }
    }
}
