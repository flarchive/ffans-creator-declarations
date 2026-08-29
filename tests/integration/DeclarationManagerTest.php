<?php

namespace FFans\CreatorDeclarations\Tests\integration;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FFans\CreatorDeclarations\CreatorDeclaration;
use FFans\CreatorDeclarations\DeclarationManager;
use PHPUnit\Framework\Attributes\Test;

class DeclarationManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ffans-creator-declarations');
    }

    #[Test]
    public function it_stores_creator_declarations_and_omits_empty_metadata(): void
    {
        [$creator, $post] = $this->postFixture();

        $this->manager()->sync($post, [
            ['key' => 'original', 'details' => '', 'title' => ''],
            ['key' => 'ai_generated', 'details' => 'Local model', 'title' => ''],
        ], $creator);

        $stored = CreatorDeclaration::query()->where('post_id', $post->id)->orderBy('id')->get();

        $this->assertCount(2, $stored);
        $this->assertSame('creator', $stored[0]->source);
        $this->assertNull($stored[0]->metadata);
        $this->assertSame(['details' => 'Local model'], $stored[1]->metadata);
        $this->assertSame($creator->id, $stored[0]->actor_id);
        $this->assertSame($post->id, $stored[0]->post_id);
    }

    #[Test]
    public function it_atomically_replaces_existing_declarations_and_marks_moderator_edits(): void
    {
        [, $post] = $this->postFixture();
        $admin = User::findOrFail(1);
        $manager = $this->manager();

        $manager->sync($post, [
            ['key' => 'original', 'details' => '', 'title' => ''],
            ['key' => 'sensitive', 'details' => '', 'title' => ''],
        ], User::findOrFail($post->user_id));

        $post->load('creatorDeclarations');
        $manager->sync($post, [
            ['key' => 'reference', 'details' => 'https://example.com/reference', 'title' => 'Source'],
        ], $admin);

        $stored = CreatorDeclaration::query()->where('post_id', $post->id)->get();

        $this->assertCount(1, $stored);
        $this->assertSame('reference', $stored[0]->declaration_key);
        $this->assertSame('moderator', $stored[0]->source);
        $this->assertSame($admin->id, $stored[0]->actor_id);
        $this->assertEquals([
            'details' => 'https://example.com/reference',
            'title' => 'Source',
        ], $stored[0]->metadata);
        $this->assertFalse($post->relationLoaded('creatorDeclarations'));
    }

    private function postFixture(): array
    {
        $this->app();

        $creator = User::factory()->create();
        $discussion = Discussion::factory()->create([
            'user_id' => $creator->id,
            'last_post_number' => 1,
        ]);
        $post = Post::factory()->create([
            'discussion_id' => $discussion->id,
            'user_id' => $creator->id,
            'number' => 1,
        ]);

        $discussion->forceFill([
            'first_post_id' => $post->id,
            'last_post_id' => $post->id,
        ])->save();

        return [$creator, $post];
    }

    private function manager(): DeclarationManager
    {
        return $this->app()->getContainer()->make(DeclarationManager::class);
    }
}
