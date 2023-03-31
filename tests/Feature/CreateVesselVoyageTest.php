<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Models\VesselVoyage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateVesselVoyageTest extends TestCase
{

    public function test_create_voyage_successful()
    {
        $voyage = [
            'title' => 'DateCheckerVoyage',
            'description' => 'Description for DateCheckerVoyage.',
            'vesselId' => 3,
            'voyageType' => 'ROUNDTRIP',
            'embarkPortId' => 1,
            'startDate' => '2023-04-01',
            'startTime' => '11:50',
            'disembarkPortId' => 3,
            'endDate' => '2023-04-10',
            'endTime' => '16:30',
        ];

        $response = $this->postJson('/api/voyages/create', $voyage);

        $response->assertStatus(200);

        $response->assertJson($voyage);
    }
}
