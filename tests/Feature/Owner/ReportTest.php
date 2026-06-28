<?php

namespace Tests\Feature\Owner;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get('/owner/reports');
        $response->assertRedirect('/login');
    }

    public function test_non_owner_cannot_access_reports(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $response = $this->actingAs($user)->get('/owner/reports');
        $response->assertStatus(403);
    }

    public function test_owner_can_access_reports_and_see_inertia_page(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $response = $this->actingAs($owner)->get('/owner/reports');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Owner/Report/Index')
            ->has('summary')
            ->has('chartData')
            ->has('topItems')
            ->has('byCategory')
            ->has('shifts')
            ->has('comparison')
        );
    }

    public function test_owner_can_export_reports_to_pdf(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $response = $this->actingAs($owner)->get('/owner/reports/export-pdf?period=today');

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=laporan-' . now()->format('Ymd') . '-' . now()->format('Ymd') . '.pdf');
    }

    public function test_owner_can_export_reports_to_csv(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $response = $this->actingAs($owner)->get('/owner/reports/export-csv?period=today');

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename="laporan-' . now()->format('Ymd') . '-' . now()->format('Ymd') . '.csv"');
    }
}
