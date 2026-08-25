<?php

namespace FFans\CreatorDeclarations\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Post\Post;
use FFans\CreatorDeclarations\DeclarationManager;
use FFans\CreatorDeclarations\DeclarationRegistry;
use FFans\CreatorDeclarations\DeclarationValidator;

class PostResourceFields
{
    public function __construct(
        protected DeclarationValidator $validator,
        protected DeclarationManager $manager,
        protected DeclarationRegistry $registry
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('canEditCreatorDeclarations')
                ->get(fn (Post $post, Context $context) => $this->canEdit($post, $context)),

            Schema\Arr::make('creatorDeclarationData')
                ->visible(false)
                ->required(fn (Context $context) => $context->creating()
                    && ! $context->internal('isFirstPost')
                    && $this->registry->isRequiredForReply())
                ->writable(fn (Post $post, Context $context) => $context->creating() || $this->canEdit($post, $context))
                ->set(function (Post $post, array $value, Context $context) {
                    $required = $context->creating()
                        ? ! $context->internal('isFirstPost') && $this->registry->isRequiredForReply()
                        : ($post->number === 1
                            ? $this->registry->isRequiredForDiscussion()
                            : $this->registry->isRequiredForReply());

                    $preservedKeys = $context->creating()
                        ? []
                        : $post->creatorDeclarations()->pluck('declaration_key')->all();

                    $declarations = $this->validator->validate($value, $required, $preservedKeys);
                    $actor = $context->getActor();

                    $post->afterSave(function (Post $savedPost) use ($declarations, $actor) {
                        $this->manager->sync($savedPost, $declarations, $actor);
                    });
                }),

            Schema\Relationship\ToMany::make('creatorDeclarations')
                ->type('creator-declarations')
                ->includable(),
        ];
    }

    protected function canEdit(Post $post, Context $context): bool
    {
        $actor = $context->getActor();

        if (! $actor->exists) {
            return false;
        }

        if ($actor->hasPermission('discussion.moderateCreatorDeclarations')) {
            return true;
        }

        return $post->user_id === $actor->id
            && (! $post->hidden_at || $post->hidden_user_id === $actor->id)
            && $actor->can('reply', $post->discussion)
            && $this->registry->allowsEditingOwn($post);
    }
}
