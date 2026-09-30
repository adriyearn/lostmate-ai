<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\AdminLog;
use App\Models\Category;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ReportActionTaken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_non_admins_cannot_access_any_admin_page(): void
    {
        $user = User::factory()->create();

        foreach ([
            'admin.dashboard', 'admin.users.index', 'admin.reports.index',
            'admin.claims.index', 'admin.categories.index', 'admin.flags.index', 'admin.logs.index',
        ] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_admin_dashboard_shows_stats(): void
    {
        LostItem::factory()->create(['status' => ItemStatus::Open]);
        FoundItem::factory()->create(['status' => ItemStatus::Returned]);

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('stats');
    }

    public function test_admin_can_search_users(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Findable Person']);
        User::factory()->create(['name' => 'Someone Else']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Findable']));

        $response->assertOk();
        $response->assertSee('Findable Person');
        $response->assertDontSee('Someone Else');
    }

    public function test_admin_can_deactivate_and_reactivate_a_user(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.users.toggle-active', $user))->assertRedirect();
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.users.toggle-active', $user))->assertRedirect();
        $this->assertTrue($user->fresh()->is_active);

        $this->assertDatabaseHas('admin_logs', ['admin_id' => $admin->id, 'action' => 'user.deactivated']);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.toggle-active', $admin))->assertForbidden();
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_change_a_users_role(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.update-role', $user), ['role' => 'admin']);

        $this->assertSame(UserRole::Admin, $user->fresh()->role);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.update-role', $admin), ['role' => 'student_staff'])
            ->assertForbidden();
    }

    public function test_admin_can_view_a_found_items_hidden_details(): void
    {
        $admin = $this->admin();
        $foundItem = FoundItem::factory()->create(['hidden_details' => 'SecretMarkXYZ']);

        $response = $this->actingAs($admin)->get(route('admin.reports.show-found', $foundItem));

        $response->assertOk();
        $response->assertSee('SecretMarkXYZ');
    }

    public function test_admin_can_close_and_delete_reports(): void
    {
        $admin = $this->admin();
        $lostItem = LostItem::factory()->create(['status' => ItemStatus::Open]);

        $this->actingAs($admin)->post(route('admin.reports.close-lost', $lostItem))->assertRedirect();
        $this->assertSame(ItemStatus::Closed, $lostItem->fresh()->status);

        $this->actingAs($admin)->delete(route('admin.reports.destroy-lost', $lostItem))->assertRedirect();
        $this->assertSoftDeleted($lostItem);
    }

    public function test_admin_can_override_a_claim_status_with_a_required_note(): void
    {
        $admin = $this->admin();
        $claim = Claim::factory()->create(['status' => ClaimStatus::Pending]);

        $response = $this->actingAs($admin)->post(route('admin.claims.override', $claim), [
            'status' => 'rejected',
            'note' => 'Fraudulent claim, verified with campus security.',
        ]);

        $response->assertRedirect();
        $this->assertSame(ClaimStatus::Rejected, $claim->fresh()->status);
        $this->assertDatabaseHas('admin_logs', ['action' => 'claim.status_overridden']);
    }

    public function test_admin_claim_override_requires_a_note(): void
    {
        $admin = $this->admin();
        $claim = Claim::factory()->create(['status' => ClaimStatus::Pending]);

        $response = $this->actingAs($admin)->post(route('admin.claims.override', $claim), [
            'status' => 'rejected',
        ]);

        $response->assertSessionHasErrors('note');
    }

    public function test_admin_can_create_edit_and_deactivate_a_category(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Umbrellas'])->assertRedirect();
        $category = Category::where('name', 'Umbrellas')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['name' => 'Umbrellas & Raincoats'])
            ->assertRedirect();
        $this->assertSame('Umbrellas & Raincoats', $category->fresh()->name);

        $this->actingAs($admin)->post(route('admin.categories.toggle-active', $category))->assertRedirect();
        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_a_category_with_items_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        LostItem::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_resolve_a_flagged_message_and_notify_the_reporter(): void
    {
        $admin = $this->admin();
        $reporter = User::factory()->create();
        $message = Message::factory()->create();
        $report = Report::factory()->create([
            'reporter_id' => $reporter->id,
            'reportable_type' => Message::class,
            'reportable_id' => $message->id,
            'reason' => ReportReason::Inappropriate,
            'status' => ReportStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.flags.update-status', $report), [
            'status' => 'action_taken',
            'admin_notes' => 'Message removed and user warned.',
        ]);

        $response->assertRedirect();
        $this->assertSame(ReportStatus::ActionTaken, $report->fresh()->status);
        $this->assertSame($admin->id, $report->fresh()->reviewed_by);
        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $reporter->id)
            ->where('type', ReportActionTaken::class)->count());
    }

    public function test_a_user_can_report_a_lost_item(): void
    {
        $reporter = User::factory()->create();
        $lostItem = LostItem::factory()->create();

        $response = $this->actingAs($reporter)->post(route('lost-items.report', $lostItem), [
            'reason' => 'spam',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reports', [
            'reportable_type' => LostItem::class,
            'reportable_id' => $lostItem->id,
            'reporter_id' => $reporter->id,
        ]);
    }

    public function test_admin_logs_are_filterable_by_admin_and_action(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::factory()->admin()->create();

        AdminLog::factory()->create(['admin_id' => $admin->id, 'action' => 'category.created']);
        AdminLog::factory()->create(['admin_id' => $otherAdmin->id, 'action' => 'user.deactivated']);

        $response = $this->actingAs($admin)->get(route('admin.logs.index', ['admin_id' => $admin->id]));

        $response->assertOk();
        $response->assertSee('category.created');
    }
}
