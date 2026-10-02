<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_sees_the_summary_with_top_locations(): void
    {
        LostItem::factory()->count(2)->create(['location_lost' => 'Library', 'date_lost' => now()]);
        FoundItem::factory()->create(['location_found' => 'library ', 'date_found' => now()]);
        FoundItem::factory()->create(['location_found' => 'Canteen', 'date_found' => now()]);

        $this->actingAs($this->admin())->get(route('admin.exports.index'))
            ->assertOk()
            ->assertSee('Reports &amp; export', false)
            ->assertSeeInOrder(['Library', '3', 'Canteen', '1']);
    }

    public function test_csv_contains_reports_in_range_but_never_private_fields(): void
    {
        $finder = User::factory()->create(['email' => 'finder@school.test']);
        FoundItem::factory()->create([
            'user_id' => $finder->id,
            'item_name' => 'Black wallet',
            'hidden_details' => 'Contains a school ID',
            'date_found' => '2026-09-10',
        ]);
        LostItem::factory()->create(['item_name' => 'Old umbrella', 'date_lost' => '2026-01-05']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.exports.csv', ['from' => '2026-09-01', 'to' => '2026-09-30']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Black wallet', $csv);
        $this->assertStringNotContainsString('Old umbrella', $csv); // outside the date range
        $this->assertStringNotContainsString('Contains a school ID', $csv);
        $this->assertStringNotContainsString('finder@school.test', $csv);
    }

    public function test_formula_like_values_are_neutralised_in_the_csv(): void
    {
        LostItem::factory()->create(['item_name' => '=HYPERLINK("http://evil.test")', 'date_lost' => now()]);

        $csv = $this->actingAs($this->admin())->get(route('admin.exports.csv'))->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_exporting_is_logged(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.exports.csv'))->streamedContent();

        $this->assertDatabaseHas('admin_logs', ['admin_id' => $admin->id, 'action' => 'report.exported']);
    }

    public function test_students_cannot_export(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.exports.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.exports.csv'))->assertForbidden();
    }
}
