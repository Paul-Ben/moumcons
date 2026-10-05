<?php

namespace Tests\Feature;

use App\Enums\NewsStatus;
use App\Models\AuditLog;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** M5 — news workflow, scheduling and public pages (PRD §16). */
class NewsTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Rbac::CONTENT_EDITOR);

        return $user->fresh();
    }

    private function writer(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $role = Role::create(['name' => 'News writer', 'guard_name' => 'web']);
        $role->givePermissionTo(['view-news', 'create-news', 'edit-news']);
        $user->assignRole($role);

        return $user->fresh();
    }

    /* --------------------------------- Admin -------------------------------- */

    public function test_editor_publishes_an_article_now(): void
    {
        $editor = $this->editor();
        $category = NewsCategory::factory()->create();

        $this->actingAs($editor)->post(route('admin.news.store'), [
            'title' => 'MOAUM opens new training centre',
            'content' => '<div>Opening <script>x()</script>today.</div>',
            'news_category_id' => $category->id,
            'status' => 'published',
            'tags' => 'training, campus',
        ])->assertRedirect();

        $article = NewsArticle::sole();
        $this->assertSame($editor->id, $article->author_id);
        $this->assertTrue($article->isPublished());
        $this->assertNotNull($article->published_at);
        $this->assertStringNotContainsString('script', $article->content);
        $this->assertSame(['training', 'campus'], $article->tags);
        $this->assertTrue(AuditLog::query()->where('action', 'news_article.created')->exists());
    }

    public function test_writer_can_submit_for_review_but_not_publish(): void
    {
        $writer = $this->writer();

        $this->actingAs($writer)->post(route('admin.news.store'), [
            'title' => 'Draft piece', 'content' => '<div>Body</div>', 'status' => 'published',
        ])->assertForbidden();

        $this->actingAs($writer)->post(route('admin.news.store'), [
            'title' => 'Review piece', 'content' => '<div>Body</div>', 'status' => 'review',
        ])->assertRedirect();

        $this->assertSame(NewsStatus::Review, NewsArticle::sole()->status);
    }

    public function test_writer_cannot_unpublish_a_live_article(): void
    {
        $writer = $this->writer();
        $article = NewsArticle::factory()->create();

        $this->actingAs($writer)->put(route('admin.news.update', $article), [
            'title' => $article->title, 'content' => '<div>Edited</div>', 'status' => 'draft',
        ])->assertForbidden();

        // Editing content while keeping the status is fine.
        $this->actingAs($writer)->put(route('admin.news.update', $article), [
            'title' => 'Fixed typo', 'content' => '<div>Edited</div>', 'status' => 'published',
        ])->assertRedirect();

        $this->assertSame('Fixed typo', $article->fresh()->title);
    }

    public function test_scheduling_requires_a_future_date(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.news.store'), [
            'title' => 'Later', 'content' => '<div>x</div>', 'status' => 'scheduled',
        ])->assertSessionHasErrors('published_at');

        $this->actingAs($editor)->post(route('admin.news.store'), [
            'title' => 'Later', 'content' => '<div>x</div>', 'status' => 'scheduled',
            'published_at' => now()->subHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('published_at');
    }

    public function test_scheduler_command_publishes_due_articles(): void
    {
        $due = NewsArticle::factory()->create(['status' => NewsStatus::Scheduled, 'published_at' => now()->subMinute()]);
        $later = NewsArticle::factory()->scheduled()->create();

        $this->artisan('news:publish-scheduled')->assertSuccessful();

        $this->assertSame(NewsStatus::Published, $due->fresh()->status);
        $this->assertSame(NewsStatus::Scheduled, $later->fresh()->status);
    }

    public function test_admin_screens_render(): void
    {
        $editor = $this->editor();
        $article = NewsArticle::factory()->create();
        NewsCategory::factory()->create(['name' => 'Announcements']);

        $this->actingAs($editor)->get(route('admin.news.index'))->assertOk()->assertSee($article->title);
        $this->actingAs($editor)->get(route('admin.news.create'))->assertOk();
        $this->actingAs($editor)->get(route('admin.news.edit', $article))->assertOk();
        $this->actingAs($editor)->get(route('admin.news-categories.index'))->assertOk()->assertSee('Announcements');
    }

    /* -------------------------------- Public -------------------------------- */

    public function test_public_listing_hides_drafts_and_future_articles(): void
    {
        NewsArticle::factory()->create(['title' => 'Live story']);
        NewsArticle::factory()->draft()->create(['title' => 'Draft story']);
        NewsArticle::factory()->scheduled()->create(['title' => 'Future story']);
        NewsArticle::factory()->create(['title' => 'Overdue scheduled', 'status' => NewsStatus::Scheduled, 'published_at' => now()->subHour()]);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Live story')
            ->assertSee('Overdue scheduled')
            ->assertDontSee('Draft story')
            ->assertDontSee('Future story');
    }

    public function test_article_page_and_404_for_drafts(): void
    {
        $article = NewsArticle::factory()->create(['content' => '<div><strong>Bold news</strong></div>', 'tags' => ['farming']]);
        $draft = NewsArticle::factory()->draft()->create();

        $this->get(route('news.show', $article))
            ->assertOk()
            ->assertSee('<strong>Bold news</strong>', escape: false)
            ->assertSee('farming');
        $this->get(route('news.show', $draft))->assertNotFound();
    }

    public function test_category_and_tag_filters(): void
    {
        $events = NewsCategory::factory()->create(['name' => 'Events']);
        NewsArticle::factory()->create(['title' => 'Event story', 'news_category_id' => $events->id, 'tags' => ['expo']]);
        NewsArticle::factory()->create(['title' => 'Other story']);

        $this->get(route('news.index', ['category' => $events->slug]))->assertSee('Event story')->assertDontSee('Other story');
        $this->get(route('news.index', ['tag' => 'expo']))->assertSee('Event story')->assertDontSee('Other story');
    }

    public function test_latest_news_on_home_page(): void
    {
        NewsArticle::factory()->create(['title' => 'Homepage headline']);

        $this->get(route('home'))->assertOk()->assertSee('Latest from MOAUM')->assertSee('Homepage headline');
    }
}
