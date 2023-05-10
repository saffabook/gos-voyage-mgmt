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
}
