<?php

namespace FFans\CreatorDeclarations\Tests\integration;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FFans\CreatorDeclarations\CreatorDeclaration;
use FFans\CreatorDeclarations\DeclarationManager;

class DeclarationManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ffans-creator-declarations');
    }

    /** @test */
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

    /** @test */
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

        $creator = User::register('declaration_creator', 'creator@example.com', 'password');
        $creator->activate();
        $creator->save();

        $discussion = Discussion::start('Declaration fixture', $creator);
        $discussion->save();

        $post = CommentPost::reply($discussion->id, 'Fixture post', $creator->id, '127.0.0.1', $creator);
        $post->number = 1;
        $post->save();

        $discussion->setFirstPost($post);
        $discussion->setLastPost($post);
        $discussion->save();

        return [$creator, $post];
    }

    private function manager(): DeclarationManager
    {
        return $this->app()->getContainer()->make(DeclarationManager::class);
    }
}
