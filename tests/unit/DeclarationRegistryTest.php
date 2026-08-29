<?php

namespace FFans\CreatorDeclarations\Tests\unit;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use FFans\CreatorDeclarations\DeclarationRegistry;
use FFans\CreatorDeclarations\Tests\Support\ArraySettingsRepository;
use PHPUnit\Framework\TestCase;

class DeclarationRegistryTest extends TestCase
{
    /** @test */
    public function it_orders_known_keys_and_appends_omitted_keys(): void
    {
        $registry = $this->registry([
            'ffans-creator-declarations.order' => 'sponsored, original,unknown,sponsored',
        ]);

        $ordered = $registry->orderedKeys();

        $this->assertSame(['sponsored', 'original'], array_slice($ordered, 0, 2));
        $this->assertSame(DeclarationRegistry::KEYS, array_values(array_intersect(DeclarationRegistry::KEYS, $ordered)));
        $this->assertCount(count(DeclarationRegistry::KEYS), $ordered);
    }

    /** @test */
    public function it_filters_disabled_keys_without_losing_the_configured_order(): void
    {
        $registry = $this->registry([
            'ffans-creator-declarations.order' => 'sponsored,original',
            'ffans-creator-declarations.enabled.sponsored' => '0',
        ]);

        $this->assertSame('original', $registry->enabledKeys()[0]);
        $this->assertNotContains('sponsored', $registry->enabledKeys());
    }

    /** @test */
    public function it_clamps_the_maximum_selection_count(): void
    {
        $this->assertSame(1, $this->registry(['ffans-creator-declarations.max' => '0'])->maxSelections());
        $this->assertSame(6, $this->registry(['ffans-creator-declarations.max' => '6'])->maxSelections());
        $this->assertSame(9, $this->registry(['ffans-creator-declarations.max' => '99'])->maxSelections());
    }

    /** @test */
    public function it_reads_required_settings_strictly(): void
    {
        $registry = $this->registry([
            'ffans-creator-declarations.required_discussion' => '1',
            'ffans-creator-declarations.required_reply' => true,
        ]);

        $this->assertTrue($registry->isRequiredForDiscussion());
        $this->assertFalse($registry->isRequiredForReply());
    }

    /** @test */
    public function it_allows_own_edits_according_to_reply_position_or_time_limit(): void
    {
        $discussion = new Discussion();
        $discussion->setRawAttributes(['last_post_number' => 2]);

        $post = new Post();
        $post->setRawAttributes(['number' => 2, 'created_at' => Carbon::now()->subMinutes(5)]);
        $post->setRelation('discussion', $discussion);

        $this->assertTrue($this->registry()->allowsEditingOwn($post));

        $discussion->last_post_number = 3;
        $this->assertFalse($this->registry()->allowsEditingOwn($post));
        $this->assertTrue($this->registry(['ffans-creator-declarations.allow_edit_own' => '10'])->allowsEditingOwn($post));

        $post->setRawAttributes(['number' => 2, 'created_at' => Carbon::now()->subMinutes(11)]);
        $this->assertFalse($this->registry(['ffans-creator-declarations.allow_edit_own' => '10'])->allowsEditingOwn($post));
        $this->assertTrue($this->registry(['ffans-creator-declarations.allow_edit_own' => '-1'])->allowsEditingOwn($post));
    }

    private function registry(array $settings = []): DeclarationRegistry
    {
        return new DeclarationRegistry(new ArraySettingsRepository($settings));
    }
}
