<?php

namespace FFans\CreatorDeclarations\Listener;

use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use Flarum\User\Exception\PermissionDeniedException;
use FFans\CreatorDeclarations\DeclarationManager;
use FFans\CreatorDeclarations\DeclarationRegistry;
use FFans\CreatorDeclarations\DeclarationValidator;
use Illuminate\Support\Arr;

class SaveCreatorDeclarations
{
    /** @var DeclarationValidator */
    protected $validator;

    /** @var DeclarationManager */
    protected $manager;

    /** @var DeclarationRegistry */
    protected $registry;

    public function __construct(
        DeclarationValidator $validator,
        DeclarationManager $manager,
        DeclarationRegistry $registry
    ) {
        $this->validator = $validator;
        $this->manager = $manager;
        $this->registry = $registry;
    }

    public function handle(Saving $event): void
    {
        $post = $event->post;

        if (! $post instanceof CommentPost) {
            return;
        }

        $attributes = Arr::get($event->data, 'attributes', []);
        $hasInput = array_key_exists('creatorDeclarationData', $attributes);
        $isNew = ! $post->exists;
        $isFirstPost = $isNew
            ? $post->discussion->first_post_id === null
            : $post->number === 1;
        $required = $isFirstPost
            ? $this->registry->isRequiredForDiscussion()
            : $this->registry->isRequiredForReply();

        if (! $hasInput && (! $isNew || ! $required)) {
            return;
        }

        if (! $isNew && ! $this->registry->canEdit($post, $event->actor)) {
            throw new PermissionDeniedException();
        }

        $input = $hasInput ? $attributes['creatorDeclarationData'] : [];

        if (! is_array($input)) {
            $input = [$input];
        }

        $preservedKeys = $isNew
            ? []
            : $post->creatorDeclarations()->pluck('declaration_key')->all();
        $declarations = $this->validator->validate($input, $required, $preservedKeys);
        $actor = $event->actor;

        $post->afterSave(function (CommentPost $savedPost) use ($declarations, $actor) {
            $this->manager->sync($savedPost, $declarations, $actor);
        });
    }
}
