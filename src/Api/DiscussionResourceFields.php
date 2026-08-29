<?php

namespace FFans\CreatorDeclarations\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use FFans\CreatorDeclarations\DeclarationManager;
use FFans\CreatorDeclarations\DeclarationRegistry;
use FFans\CreatorDeclarations\DeclarationValidator;

class DiscussionResourceFields
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
            Schema\Arr::make('creatorDeclarationData')
                ->visible(false)
                ->required(fn (Context $context) => $context->creating() && $this->registry->isRequiredForDiscussion())
                ->writable(fn (Discussion $discussion, Context $context) => $context->creating())
                ->set(function (Discussion $discussion, array $value, Context $context) {
                    $declarations = $this->validator->validate($value, $this->registry->isRequiredForDiscussion());
                    $actor = $context->getActor();

                    // Flarum saves a new discussion before creating its first post,
                    // then saves it again after attaching that post.
                    $discussion->afterSave(function (Discussion $savedDiscussion) use ($declarations, $actor) {
                        $savedDiscussion->afterSave(function (Discussion $discussionWithFirstPost) use ($declarations, $actor) {
                            $this->manager->sync($discussionWithFirstPost->firstPost, $declarations, $actor);
                        });
                    });
                }),
        ];
    }
}
