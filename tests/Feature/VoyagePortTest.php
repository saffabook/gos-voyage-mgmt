<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VoyagePort;
use Illuminate\Foundation\Testing\WithFaker;

class VoyagePortTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanCreatePort()
    {
        $port = [
            'companyId'   => '1',
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
