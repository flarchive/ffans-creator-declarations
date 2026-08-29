<?php

namespace FFans\CreatorDeclarations;

use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;

class DeclarationRegistry
{
    public const KEYS = [
        'original',
        'repost',
        'reference',
        'ai_generated',
        'fictional',
        'personal_opinion',
        'professional',
        'sensitive',
        'self_promotion',
        'sponsored',
    ];

    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function enabledKeys(): array
    {
        return array_values(array_filter(
            $this->orderedKeys(),
            fn (string $key) => $this->settings->get("ffans-creator-declarations.enabled.$key", '1') === '1'
        ));
    }

    public function orderedKeys(): array
    {
        $configured = array_filter(array_map(
            'trim',
            explode(',', (string) $this->settings->get('ffans-creator-declarations.order', implode(',', self::KEYS)))
        ));

        $known = array_values(array_unique(array_filter(
            $configured,
            fn (string $key) => in_array($key, self::KEYS, true)
        )));

        return array_merge($known, array_values(array_diff(self::KEYS, $known)));
    }

    public function maxSelections(): int
    {
        return max(1, min(9, (int) $this->settings->get('ffans-creator-declarations.max', '5')));
    }

    public function allowsEditingOwn(Post $post): bool
    {
        $allowEditing = $this->settings->get('ffans-creator-declarations.allow_edit_own', 'reply');

        return $allowEditing === '-1'
            || ($allowEditing === 'reply' && $post->number >= $post->discussion->last_post_number)
            || (is_numeric($allowEditing) && $post->created_at->diffInMinutes(null, true) < (int) $allowEditing);
    }

    public function isRequiredForDiscussion(): bool
    {
        return $this->settings->get('ffans-creator-declarations.required_discussion', '0') === '1';
    }

    public function isRequiredForReply(): bool
    {
        return $this->settings->get('ffans-creator-declarations.required_reply', '0') === '1';
    }
}
