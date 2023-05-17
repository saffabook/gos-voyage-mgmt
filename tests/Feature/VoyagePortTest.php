<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VoyagePort;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Vessel;
use Carbon\Carbon;
use App\Models\VesselVoyage;
use App\Helpers\GenerateVoyageId;

class VoyagePortTest extends TestCase
{
    use RefreshDatabase;

    /*
     * TODO: Add docblock
     */
    public function testUserCanCreatePort()
    {
        $requests = [
            [
                'companyId'   => '1',
                'title'       => 'Helsinki',
            ],
            [
                'companyId'   => '1',
                'title'       => 'Helsinki',
            ],
            [
                'companyId'   => '2',
                'title'       => 'Helsinki',
            ],
            [
                'title'       => 'Helsinki',
            ],
        ];

        foreach ($requests as $key => $request) {
            $jsonResponse = $this->postJson('/api/ports/create', $request);

            if (isset($jsonResponse['error']) || $jsonResponse->status() > 400) {
                if ($jsonResponse->status() === 500) {
                    $jsonResponse->assertStatus(500);
                } else {
                    $jsonResponse->assertStatus(422);
                }
            } else {
                $jsonResponse->assertStatus(200);
                $jsonResponse->assertJson([
                    'data' => $request
                ]);
                $this->assertDatabaseHas('voyage_ports', $request);
            }
        }
    }

    public function testUserCannotCreatePortWithExistingTitle()
    {
        VoyagePort::factory()->create([
            'companyId' => '1',
            'title'     => 'Helsinki'
        ]);

        $port = [
            'companyId'   => '1',
            'title'       => 'Helsinki',
            'description' => 'Description for Helsinki port.',
            'directions'  => 'Directions for how to get to port of Helsinki.'
        ];

        $jsonResponse = $this->postJson('/api/ports/create', $port);

        if (isset($jsonResponse['error'])) {
            $jsonResponse->assertStatus(422);

            $this->assertSame(
                $jsonResponse['error']['title'][0],
                'You have already created a port with that name.'
            );
        }
    }

    public function testUserCanCreatePortWithExistingTitleButUniqueCompanyId()
    {
        VoyagePort::factory()->create([
            'companyId' => '1',
            'title'     => 'Helsinki'
        ]);

        $port = [
            'companyId'   => '2',
            'title'       => 'Helsinki',
            'description' => 'Description for Helsinki port.',
            'directions'  => 'Directions for how to get to port of Helsinki.'
        ];

        $jsonResponse = $this->postJson('/api/ports/create', $port);

        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson([
            'data' => $port
        ]);
        $this->assertDatabaseHas('voyage_ports', $port);
    }

    public function testUserCanGetTheirOwnPort()
    {
        $port = VoyagePort::factory()->create([
            'companyId' => '1',
        ]);

        $request = [
            'companyId' => '1',
        ];

        $jsonResponse = $this->postJson('/api/ports/get/'.$port->id, $request);

        $jsonResponse->assertStatus(200)
            ->assertJson([
                'data' => $port->toArray()
            ]);
    }

    public function testUserCannotGetOtherUsersPorts()
    {
        $companyTwoPort = VoyagePort::factory()->create([
            'companyId' => '2',
            'title'     => 'Helsinki'
        ]);

        $request = [
            'companyId' => '1',
        ];

        $jsonResponse = $this->postJson(
            '/api/ports/get/'.$companyTwoPort->id, $request
        );

        $jsonResponse->assertStatus(422)
            ->assertJsonMissingExact($companyTwoPort->toArray())
            ->assertJson([
                'error' => 'Port not found'
            ]);
    }

    public function testUserAlertedIfPortDoesNotExist()
    {
        $request = [
            'companyId' => '1',
        ];

        $jsonResponse = $this->postJson('/api/ports/get/42', $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Port not found'
            ]);
    }

    public function testUserCanUpdateTheirOwnPort()
    {
        $companyOnePort = VoyagePort::factory()->create([
            'companyId' => '1',
        ]);

        $request = [
            'id'        => $companyOnePort->id,
            'companyId' => $companyOnePort->companyId,
            'title'     => 'Edited Title'
        ];

        $jsonResponse = $this->postJson('/api/ports/update', $request);

        $jsonResponse->assertStatus(200)
            ->assertJson([
                'data' => $request
            ]);

        $this->assertDatabaseHas('voyage_ports', [
            'title' => 'Edited Title',
        ]);
    }

    public function testUserCannotUpdatePortTitleToBeSameAsExistingPortTitle()
    {
        $existingPort = VoyagePort::factory()->create([
            'companyId' => '1',
        ]);

        $request = [
            'id'        => '2',
            'companyId' => '1',
            'title'     => $existingPort->title
        ];

        $jsonResponse = $this->postJson('/api/ports/update', $request);

        $jsonResponse->assertStatus(422);

        $this->assertSame(
            $jsonResponse['error']['title'][0],
            'You have already created a port with that name.'
        );
    }

    public function testUserCannotUpdateOtherUsersPorts()
    {
        $companyOnePort = VoyagePort::factory()->create([
            'companyId' => '1',
            'title'     => 'Helsinki'
        ]);

        $request = [
            'id'        => $companyOnePort->id,
            'companyId' => '2',
            'title'     => 'Porvoo'
        ];

        $jsonResponse = $this->postJson('/api/ports/update', $request);

        $jsonResponse->assertStatus(422)
            ->assertJson([
                'error' => 'Port not found.'
            ]);
    }

    public function testUserCanUpdateSpecificPortToKeepSameTitle()
    {
        $companyOnePort = VoyagePort::factory()->create([
            'companyId' => '1'
        ]);

        $request = [
            'id'          => $companyOnePort->id,
            'companyId'   => $companyOnePort->companyId,
            'title'       => $companyOnePort->title,
            'description' => 'Edited port description for testing purposes.',
            'directions'  => 'Edited directions.'
        ];

        $jsonResponse = $this->postJson('/api/ports/update', $request);

        $jsonResponse->assertStatus(200)
            ->assertJson([
                'data' => $request
            ]);

        $this->assertDatabaseHas('voyage_ports', $request);
    }

    public function testUserCanDeleteTheirOwnPort()
    {
        $companyOnePort = VoyagePort::factory()->create([
            'companyId' => '1'
        ]);

        $request = [
            'companyId' => $companyOnePort->companyId
        ];

        $jsonResponse = $this->postJson(
            '/api/ports/delete/'.$companyOnePort->id, $request
        );

        $jsonResponse->assertStatus(200)
            ->assertJsonPath('data.message', 'Port deleted successfully');

        $this->assertDatabaseMissing(
            'voyage_ports', $companyOnePort->toArray()
        )->assertDatabaseCount('voyage_ports', 0);
    }

    public function testUserCannotDeleteOtherUsersPorts()
    {
        $companyOnePort = VoyagePort::factory()->create([
            'companyId' => '1',
        ]);

        $request = [
            'companyId' => '2',
        ];

        $jsonResponse = $this->postJson(
            '/api/ports/delete/'.$companyOnePort->id, $request
        );

        $jsonResponse->assertStatus(422)
            ->assertJsonMissingExact($companyOnePort->toArray())
            ->assertJson([
                'error' => 'Port not found'
            ]);

        $this->assertDatabaseHas('voyage_ports', [
            'id' => $companyOnePort->id,
        ]);
    }

    public function testUserCannotDeleteActivePort()
    {
        $vessel  = Vessel::factory()->create(['companyId' => '1']);
        $port1   = VoyagePort::factory()->create(['companyId' => '1']);
        $port2   = VoyagePort::factory()->create(['companyId' => '1']);
        $today   = Carbon::now();
        $request = ['companyId' => '1'];

        VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $request['companyId'],
            'voyageReferenceNumber' => GenerateVoyageId::execute(
                $request['companyId']
            )
        ]);

        $jsonResponse = $this->postJson(
            '/api/ports/delete/'.$port1->id, $request
        );

        $jsonResponse->assertStatus(422)
            ->assertJson(['error' => 'Cannot delete. Port is in use.']);

        $this->assertDatabaseHas('voyage_ports', ['id' => $port1->id]);
    }

    public function testUserCanDeleteInactivePort()
    {
        $vessel  = Vessel::factory()->create(['companyId' => '1']);
        $port1   = VoyagePort::factory()->create(['companyId' => '1']);
        $port2   = VoyagePort::factory()->create(['companyId' => '1']);
        $today   = Carbon::now();
        $request = ['companyId' => '1'];

        VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->addDays(2)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDays(12)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $request['companyId'],
            'voyageReferenceNumber' => GenerateVoyageId::execute(
                $request['companyId']
            )
        ]);

        $jsonResponse = $this->postJson(
            '/api/ports/delete/'.$port2->id, $request
        );

        $jsonResponse->assertStatus(200)
            ->assertJsonPath('data.message', 'Port deleted successfully');

        $this->assertDatabaseMissing('voyage_ports', $port2->toArray())
             ->assertDatabaseCount('voyage_ports', 1);
    }

    public function testUserCanForceDeleteActivePort()
    {
        $vessel  = Vessel::factory()->create(['companyId' => '1']);
        $port1   = VoyagePort::factory()->create(['companyId' => '1']);
        $port2   = VoyagePort::factory()->create(['companyId' => '1']);
        $today   = Carbon::now();

        $request = [
            'companyId'   => '1',
            'forceAction' => '1'
        ];

        VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            'startDate'             => $today->subDay()->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            'endDate'               => $today->addDay()->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $request['companyId'],
            'voyageReferenceNumber' => GenerateVoyageId::execute(
                $request['companyId']
            )
        ]);

        $jsonResponse = $this->postJson(
            '/api/ports/delete/'.$port1->id, $request
        );

        $jsonResponse->assertStatus(200)
            ->assertJsonPath('data.message', 'Port deleted successfully');

        $this->assertDatabaseMissing('voyage_ports', $port1->toArray())
             ->assertDatabaseCount('voyage_ports', 1);
    }

    public function testVoyageWithDeletedPortIsNull()
    {
        $vessel  = Vessel::factory()->create(['companyId' => '1']);
        $port1   = VoyagePort::factory()->create(['companyId' => '1']);
        $port2   = VoyagePort::factory()->create(['companyId' => '1']);
        // $today   = Carbon::now();
        // $request = ['companyId' => '1'];
        $request = [
            'companyId'   => '1',
            'forceAction' => '1'
        ];

        $testVoyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $port1->id,
            // 'startDate'             => $today->addDays(22)->toDateString(),
            'startDate'             => '2023-05-20',
            'startTime'             => '11:50',
            'disembarkPortId'       => $port2->id,
            // 'endDate'               => $today->addDays(24)->toDateString(),
            'endDate'               => '2023-05-22',
            'endTime'               => '16:30',
            'companyId'             => $request['companyId'],
            'voyageReferenceNumber' => GenerateVoyageId::execute(
                $request['companyId']
            )
        ]);

        $jsonResponse = $this->postJson(
            // '/api/ports/delete/'.$port2->id, $request
            '/api/ports/delete/'.$testVoyage->embarkPortId, $request
        );

        var_dump($jsonResponse);

        $jsonResponse->assertStatus(200)
            ->assertJsonPath('data.message', 'Port deleted successfully');

        $this->assertDatabaseHas('vessel_voyages', [
            'id' => $testVoyage->id,
            'embarkPortId' => null,
            'disembarkPortId' => $testVoyage->disembarkPortId
        ]);
    }
}
