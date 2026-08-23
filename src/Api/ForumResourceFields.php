<?php

namespace FFans\CreatorDeclarations\Api;

use Flarum\Api\Schema;
use FFans\CreatorDeclarations\DeclarationRegistry;

class ForumResourceFields
{
    public function __construct(protected DeclarationRegistry $registry)
    {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('creatorDeclarationsRequiredForDiscussion')
                ->get(fn () => $this->registry->isRequiredForDiscussion()),
            Schema\Boolean::make('creatorDeclarationsRequiredForReply')
                ->get(fn () => $this->registry->isRequiredForReply()),
            Schema\Integer::make('creatorDeclarationsMax')
                ->get(fn () => $this->registry->maxSelections()),
        ];
    }
}
