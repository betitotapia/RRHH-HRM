<?php

namespace Tests\Feature;

use App\Models\WorkLocation;
use App\Services\GeofencingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeofencingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_the_nearest_active_location_for_mixed_attendance(): void
    {
        WorkLocation::create([
            'name' => 'Sede lejana', 'code' => 'LEJ',
            'latitude' => 19.50000000, 'longitude' => -99.20000000,
            'radius_meters' => 100, 'qr_secret' => 'far', 'is_active' => true,
        ]);
        $near = WorkLocation::create([
            'name' => 'Sede cercana', 'code' => 'CER',
            'latitude' => 19.43260000, 'longitude' => -99.13320000,
            'radius_meters' => 150, 'qr_secret' => 'near', 'is_active' => true,
        ]);

        $result = app(GeofencingService::class)->nearestActiveLocation(19.43265000, -99.13325000);

        $this->assertSame($near->id, $result['location']->id);
        $this->assertTrue($result['within']);
    }

    public function test_it_ignores_inactive_locations(): void
    {
        WorkLocation::create([
            'name' => 'Sede inactiva', 'code' => 'INA',
            'latitude' => 19.43260000, 'longitude' => -99.13320000,
            'radius_meters' => 150, 'qr_secret' => 'inactive', 'is_active' => false,
        ]);

        $this->assertNull(app(GeofencingService::class)->nearestActiveLocation(19.43260000, -99.13320000));
    }
}
