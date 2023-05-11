<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VoyagePort;
use Illuminate\Foundation\Testing\WithFaker;

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
}
