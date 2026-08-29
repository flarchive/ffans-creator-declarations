<?php

namespace FFans\CreatorDeclarations\Api;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Post\Post;
use FFans\CreatorDeclarations\DeclarationRegistry;

class PostAttributes
{
    /** @var DeclarationRegistry */
    protected $registry;

    public function __construct(DeclarationRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function __invoke(AbstractSerializer $serializer, Post $post)
    {
        return $this->registry->canEdit($post, $serializer->getActor());
    }
}
