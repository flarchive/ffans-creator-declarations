<?php

namespace FFans\CreatorDeclarations\Api\Resource;

use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use FFans\CreatorDeclarations\CreatorDeclaration;

class CreatorDeclarationResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'creator-declarations';
    }

    public function model(): string
    {
        return CreatorDeclaration::class;
    }

    public function endpoints(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('key')->property('declaration_key'),
            Schema\Str::make('source'),
            Schema\Arr::make('metadata'),
            Schema\DateTime::make('createdAt'),
        ];
    }
}
