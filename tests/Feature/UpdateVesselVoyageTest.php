<?php

namespace Tests\Feature;

use App\Helpers\GenerateVoyageId;
use App\Models\Vessel;
use App\Models\VesselVoyage;
use App\Models\VoyagePort;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateVesselVoyageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure user can update voyage where company, vessel and ports are all valid.
     *
     * @return void
     */
    public function testUserCanUpdateVoyageSuccessfully()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $otherVessel   = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $otherPort     = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
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
            'title'           => 'Updated Voyage Title',
            'description'     => 'Updated description for Test Voyage',
            'vesselId'        => $otherVessel->id,
            'embarkPortId'    => $otherPort->id,
            'disembarkPortId' => $otherPort->id,
            'voyageStatus'    => 'CANCELLED',
            'companyId'       => $companyId
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyage->id, $request);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment(['title'           => $request['title']])
                     ->assertJsonFragment(['description'     => $request['description']])
                     ->assertJsonFragment(['vesselId'        => $request['vesselId']])
                     ->assertJsonFragment(['embarkPortId'    => $request['embarkPortId']])
                     ->assertJsonFragment(['disembarkPortId' => $request['disembarkPortId']])
                     ->assertJsonFragment(['voyageStatus'    => $request['voyageStatus']])
                     ->assertJsonFragment(['companyId'       => $request['companyId']])
                     ->assertJsonFragment(['message'         => 'The voyage was updated.']);

        $this->assertDatabasehas('vessel_voyages', [
            'title'           => $request['title'],
            'description'     => $request['description'],
            'vesselId'        => $request['vesselId'],
            'embarkPortId'    => $request['embarkPortId'],
            'disembarkPortId' => $request['disembarkPortId'],
            'voyageStatus'    => $request['voyageStatus'],
            'companyId'       => $request['companyId']
        ]);
    }


    /**
     * Ensure user cannot update another company's voyage.
     *
     * @return void
     */
    public function testUserCannotUpdateOtherUsersVoyages()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
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
            'title'     => 'Updated Voyage Title',
            'companyId' => 2
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyage->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'Voyage not found.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'title' => $request['title']
        ]);
    }


    /**
     * Ensure user cannot update a voyage to use another company's vessel.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageToUseVesselBelongingToAnotherUser()
    {
        $companyOneId     = 1;
        $companyTwoId     = 2;
        $companyOneVessel = Vessel::factory()->create(['companyId' => $companyOneId]);
        $companyTwoVessel = Vessel::factory()->create(['companyId' => $companyTwoId]);
        $embarkPort       = VoyagePort::factory()->create(['companyId' => $companyOneId]);
        $disembarkPort    = VoyagePort::factory()->create(['companyId' => $companyOneId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $companyOneVessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(12)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(22)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyOneId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyOneId)
        ]);

        $request = [
            'title'     => 'Updated title',
            'vesselId'  => $companyTwoVessel->id,
            'companyId' => $companyOneId
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyage->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The requested vessel is invalid.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'title' => $request['title']
        ]);
    }


    /**
     * Ensure user cannot update a voyage to use another company's ports.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageToUsePortsBelongingToAnotherUser()
    {
        $companyOneId   = 1;
        $companyTwoId   = 2;
        $vessel         = Vessel::factory()->create(['companyId' => $companyOneId]);
        $embarkPort     = VoyagePort::factory()->create(['companyId' => $companyOneId]);
        $disembarkPort  = VoyagePort::factory()->create(['companyId' => $companyOneId]);
        $companyTwoPort = VoyagePort::factory()->create(['companyId' => $companyTwoId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(12)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(22)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyOneId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyOneId)
        ]);

        $request = [
            'title'        => 'Updated title',
            'embarkPortId' => $companyTwoPort->id,
            'companyId'    => $companyOneId
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyage->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'Port is invalid.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'title' => $request['title']
        ]);
    }


    /**
     * Ensure user cannot update voyage startDate to be later than endDate.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageStartDateToBeAfterEndDate()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
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
            'companyId' => 1,
            'startDate' => Carbon::now()->addDays(24)->toDateString()
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyage->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The voyage start date cannot be set later than the voyage end date.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'startDate' => $request['startDate']
        ]);
    }


    /**
     * Ensure user cannot update voyage endDate to be earlier than startDate.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageEndDateToBeBeforeStartDate()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        $voyage = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
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
            'companyId' => 1,
            'endDate'   => Carbon::now()->subDays(22)->toDateString()
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyage->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The voyage end date cannot be set earlier than the voyage start date.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'endDate' => $request['endDate']
        ]);
    }


    /**
     * Ensure user cannot update voyage vesselId where vessel is already booked.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageWithVesselThatIsBookedForAnotherVoyage()
    {
        $companyId     = 1;
        $vesselOne     = Vessel::factory()->create(['companyId' => $companyId]);
        $vesselTwo     = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $companyId]);

        $vesselOneVoyage = VesselVoyage::create([
            'title'                 => 'Vessel One Voyage',
            'description'           => 'Description for Vessel One Voyage',
            'vesselId'              => $vesselOne->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(23)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(42)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $vesselTwoVoyage = VesselVoyage::create([
            'title'                 => 'Vessel Two Voyage',
            'description'           => 'Description for Vessel Two Voyage',
            'vesselId'              => $vesselTwo->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(27)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(47)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'title'     => 'Updated title',
            'companyId' => 1,
            'vesselId'  => $vesselOneVoyage->vesselId
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $vesselTwoVoyage->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The requested vessel is already booked for this time.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'title' => $request['title']
        ]);
    }


    /**
     * Ensure user cannot update voyage startDate to conflict with an existing
     * voyage for the same vessel.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageStartDateToOverlapExistingVoyageForSameVessel()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        VesselVoyage::create([
            'title'                 => 'DB Voyage',
            'description'           => 'Description for DB Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(23)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(42)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $voyageToUpdate = VesselVoyage::create([
            'title'                 => 'Test Voyage',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(46)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(84)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'companyId' => 1,
            'startDate' => Carbon::now()->addDays(40)->toDateString()
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyageToUpdate->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The vessel is booked on another voyage for this time.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'startDate' => $request['startDate']
        ]);
    }


    /**
     * Ensure user cannot update voyage endDate to conflict with an existing
     * voyage for the same vessel.
     *
     * @return void
     */
    public function testUserCannotUpdateVoyageEndDateToOverlapExistingVoyageForSameVessel()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $vessel->companyId]);

        VesselVoyage::create([
            'title'           => 'DB Voyage',
            'description'     => 'Description for DB Voyage',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDays(46)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(84)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $voyageToUpdate = VesselVoyage::create([
            'title'           => 'Test Voyage',
            'description'     => 'Description for Test Voyage',
            'vesselId'        => $vessel->id,
            'voyageType'      => 'ROUNDTRIP',
            'embarkPortId'    => $embarkPort->id,
            'startDate'       => Carbon::now()->addDays(23)->toDateString(),
            'startTime'       => '11:50',
            'disembarkPortId' => $disembarkPort->id,
            'endDate'         => Carbon::now()->addDays(42)->toDateString(),
            'endTime'         => '16:30',
            'companyId'       => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'companyId' => 1,
            'endDate'   => Carbon::now()->addDays(47)->toDateString()
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyageToUpdate->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'The vessel is booked on another voyage for this time.'
                     ]);

        $this->assertDatabaseMissing('vessel_voyages', [
            'endDate' => $request['endDate']
        ]);
    }


    /**
     * Ensure user receives a warning if requesting to update a voyage title to
     * be identical to an existing voyage title for the same company.
     *
     * @return void
     */
    public function testUserWarnedIfUpdatingVoyageWithExistingTitle()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $companyId]);

        VesselVoyage::create([
            'title'                 => 'Test Voyage Title',
            'description'           => 'Description for DB Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(46)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(84)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $voyageToUpdate = VesselVoyage::create([
            'title'                 => 'Title To Be Updated',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(23)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(42)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'companyId' => 1,
            'title'     => 'test voyage title'
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyageToUpdate->id, $request);

        $jsonResponse->assertStatus(422)
                     ->assertJson([
                        'error' => 'You have already created a voyage with that title. Please confirm you would like to update this voyage with the duplicate title.'
                     ]);
    }


    /**
     * Ensure user can update a voyage title to duplicate an existing voyage
     * title for the same company if sending a `forceAction` with the request.
     *
     * @return void
     */
    public function testUserCanUpdateVoyageToUseExistingTitleWithForceAction()
    {
        $companyId     = 1;
        $vessel        = Vessel::factory()->create(['companyId' => $companyId]);
        $embarkPort    = VoyagePort::factory()->create(['companyId' => $companyId]);
        $disembarkPort = VoyagePort::factory()->create(['companyId' => $companyId]);

        $voyageInDb = VesselVoyage::create([
            'title'                 => 'Test Voyage Title',
            'description'           => 'Description for DB Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(46)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(84)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $voyageToUpdate = VesselVoyage::create([
            'title'                 => 'Title To Be Updated',
            'description'           => 'Description for Test Voyage',
            'vesselId'              => $vessel->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => $embarkPort->id,
            'startDate'             => Carbon::now()->addDays(23)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => $disembarkPort->id,
            'endDate'               => Carbon::now()->addDays(42)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId)
        ]);

        $request = [
            'companyId'   => 1,
            'title'       => 'test voyage title',
            'forceAction' => 1
        ];

        $jsonResponse = $this->postJson('/api/voyages/update/' . $voyageToUpdate->id, $request);

        $jsonResponse->assertStatus(200)
                     ->assertJsonStructure(['data'])
                     ->assertJsonFragment([
                        'title' => $request['title']
                    ]);

        $this->assertDatabaseHas('vessel_voyages', [
            'title' => $voyageInDb['title'],
            'title' => $request['title']
        ]);
    }
}
