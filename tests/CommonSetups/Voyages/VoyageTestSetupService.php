<?php

namespace Tests\CommonSetups\Voyages;

use App\Helpers\GenerateVoyageId;
use App\Models\Vessel;
use App\Models\VesselCabin;
use App\Models\VesselVoyage;
use App\Models\VoyageCabinPrice;
use App\Models\VoyagePort;
use Carbon\Carbon;

/**
 * Service class for creating common voyage data for tests
 */
class VoyageTestSetupService
{
    /**
     * Function to generate a port
     *
     * @param int $companyId
     * @return VoyagePort
     */
    public static function createTestDataPort($companyId)
    {
        return VoyagePort::factory()->create(['companyId' => $companyId]);
    }

    /**
     * Function to generate vessel with cabin
     *
     * @param int $companyId
     * @return array
     */
    public static function createTestDataVesselCabin($companyId)
    {
        $vessel = Vessel::factory()->create(['companyId' => $companyId]);

        return [
            'vessel' => $vessel,
            'cabin'  => VesselCabin::factory()->create(['vessel_id' => $vessel->id]),
        ];
    }

    /**
     * Function to generate an active voyage
     *
     * @param int $companyId
     * @param boolean $voyageData
     * @return VesselVoyage
     */
    public static function createTestDataVoyage($companyId, $voyageData = false)
    {
        $today = Carbon::now();

        return VesselVoyage::create([
            'title'                 => $voyageData['title'] ?? 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => self::createTestDataVesselCabin($companyId)['vessel']->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => self::createTestDataPort($companyId)->id,
            // 'startDate'             => $today->subDays(2)->toDateString(),
            'startDate'             => $today->copy()->subDays(2)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => self::createTestDataPort($companyId)->id,
            // 'endDate'               => $today->addDays(2)->toDateString(),
            'endDate'               => $today->copy()->addDays(2)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);
    }

    /**
     * Function to generate an inactive voyage
     *
     * @param int $companyId
     * @param boolean $voyageData
     * @return VesselVoyage
     */
    public static function createTestDataVoyageInactive($companyId, $voyageData = false)
    {
        $today = Carbon::now();

        return VesselVoyage::create([
            'title'                 => $voyageData['title'] ?? 'Test Voyage',
            'description'           => 'Description for TestVoyage.',
            'vesselId'              => self::createTestDataVesselCabin($companyId)['vessel']->id,
            'voyageType'            => 'ROUNDTRIP',
            'embarkPortId'          => self::createTestDataPort($companyId)->id,
            'startDate'             => $today->copy()->subDays(20)->toDateString(),
            'startTime'             => '11:50',
            'disembarkPortId'       => self::createTestDataPort($companyId)->id,
            'endDate'               => $today->copy()->subDays(10)->toDateString(),
            'endTime'               => '16:30',
            'companyId'             => $companyId,
            'voyageReferenceNumber' => GenerateVoyageId::execute($companyId),
        ]);
    }
}
