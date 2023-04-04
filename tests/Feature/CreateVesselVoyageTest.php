<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vessel;
use App\Models\VoyagePort;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateVesselVoyageTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_initial_voyage_successful()
    {
        $embarkPort = VoyagePort::factory()->create();
        $disembarkPort = VoyagePort::factory()->create();
        $vessel = Vessel::factory()->create();

        $voyage = [
            'title' => 'DateCheckerVoyage',
            'description' => 'Description for DateCheckerVoyage.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-01',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-10',
            'endTime' => '16:30',
        ];

        $response = $this->postJson('/api/voyages/create', $voyage);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => $voyage
        ]);
    }

    public function test_create_voyage_double_booking_vessel_scenario_one()
    {
        $embarkPort = VoyagePort::factory()->create();
        $disembarkPort = VoyagePort::factory()->create();
        $vessel = Vessel::factory()->create();

        $initialVoyage = [
            'title' => 'DateCheckerVoyage',
            'description' => 'Description for DateCheckerVoyage.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-01',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-10',
            'endTime' => '16:30',
        ];
        $initialResponse = $this->postJson('/api/voyages/create', $initialVoyage);
        $initialResponse->assertStatus(200);
        $initialResponse->assertJson([
            'data' => $initialVoyage
        ]);

        $overlapVoyage = [
            'title' => 'OverlapOneTest',
            'description' => 'This is the description for OverlapOneTest.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-03-30',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-01',
            'endTime' => '16:30',
        ];

        $overlapResponse = $this->postJson('/api/voyages/create', $overlapVoyage);
        $overlapResponse->assertStatus(422);
        $this->assertSame(
            $overlapResponse['error'],
            'The vessel is already booked for this time'
        );
    }

    public function test_create_voyage_double_booking_vessel_scenario_two()
    {
        $embarkPort = VoyagePort::factory()->create();
        $disembarkPort = VoyagePort::factory()->create();
        $vessel = Vessel::factory()->create();

        $initialVoyage = [
            'title' => 'DateCheckerVoyage',
            'description' => 'Description for DateCheckerVoyage.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-01',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-10',
            'endTime' => '16:30',
        ];
        $initialResponse = $this->postJson('/api/voyages/create', $initialVoyage);
        $initialResponse->assertStatus(200);
        $initialResponse->assertJson([
            'data' => $initialVoyage
        ]);

        $overlapVoyage = [
            'title' => 'OverlapTwoTest',
            'description' => 'This is the description for OverlapTwoTest.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-03-30',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-02',
            'endTime' => '16:30',
        ];

        $overlapResponse = $this->postJson('/api/voyages/create', $overlapVoyage);
        $overlapResponse->assertStatus(422);
        $this->assertSame(
            $overlapResponse['error'],
            'The vessel is already booked for this time'
        );
    }

    public function test_create_voyage_double_booking_vessel_scenario_three()
    {
        $embarkPort = VoyagePort::factory()->create();
        $disembarkPort = VoyagePort::factory()->create();
        $vessel = Vessel::factory()->create();

        $initialVoyage = [
            'title' => 'DateCheckerVoyage',
            'description' => 'Description for DateCheckerVoyage.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-01',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-10',
            'endTime' => '16:30',
        ];
        $initialResponse = $this->postJson('/api/voyages/create', $initialVoyage);
        $initialResponse->assertStatus(200);
        $initialResponse->assertJson([
            'data' => $initialVoyage
        ]);

        $overlapVoyage = [
            'title' => 'OverlapThreeTest',
            'description' => 'This is the description for OverlapThreeTest.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-09',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-12',
            'endTime' => '16:30',
        ];

        $overlapResponse = $this->postJson('/api/voyages/create', $overlapVoyage);
        $overlapResponse->assertStatus(422);
        $this->assertSame(
            $overlapResponse['error'],
            'The vessel is already booked for this time'
        );
    }

    public function test_create_voyage_double_booking_vessel_scenario_four()
    {
        $embarkPort = VoyagePort::factory()->create();
        $disembarkPort = VoyagePort::factory()->create();
        $vessel = Vessel::factory()->create();

        $initialVoyage = [
            'title' => 'DateCheckerVoyage',
            'description' => 'Description for DateCheckerVoyage.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-01',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-10',
            'endTime' => '16:30',
        ];
        $initialResponse = $this->postJson('/api/voyages/create', $initialVoyage);
        $initialResponse->assertStatus(200);
        $initialResponse->assertJson([
            'data' => $initialVoyage
        ]);

        $overlapVoyage = [
            'title' => 'OverlapFourTest',
            'description' => 'This is the description for OverlapFourTest.',
            'vesselId' => $vessel->id,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => $embarkPort->id,
            'startDate' => '2023-04-10',
            'startTime' => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate' => '2023-04-12',
            'endTime' => '16:30',
        ];

        $overlapResponse = $this->postJson('/api/voyages/create', $overlapVoyage);
        $overlapResponse->assertStatus(422);
        $this->assertSame(
            $overlapResponse['error'],
            'The vessel is already booked for this time'
        );
    }
}
