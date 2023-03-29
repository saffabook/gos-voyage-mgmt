<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Models\VesselVoyage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateVesselVoyageTest extends TestCase
{

    public function can_get_voyage_by_id()
    {
        // $voyage = VesselVoyage::with('embarkPort', 'disembarkPort')
        //     ->where('voyageReferenceNumber', $voyageReferenceNumber)
        //     ->first();

        // $post = UserFactory::factory()->create();
        $user = factory(User::class)->make();

        // $response = $this->post('/voyages/get/{id}');

        $response->assertStatus(200);
    }

    // public function can_create_a_voyage()
    // {
    //     $response = $this->post('/voyages/create');

    //     $response->assertStatus(200);
    // }
}
