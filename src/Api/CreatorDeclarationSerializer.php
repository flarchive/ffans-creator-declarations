<?php

namespace FFans\CreatorDeclarations\Api;

use Flarum\Api\Serializer\AbstractSerializer;

class CreatorDeclarationSerializer extends AbstractSerializer
{
    protected $type = 'creator-declarations';

    protected function getDefaultAttributes($declaration)
    {
        return [
            'key' => $declaration->declaration_key,
            'source' => $declaration->source,
            'metadata' => $declaration->metadata,
            'createdAt' => $this->formatDate($declaration->created_at),
        ];
    }
}
