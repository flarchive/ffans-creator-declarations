<?php

namespace FFans\CreatorDeclarations;

use Flarum\Post\Post;
use Flarum\User\User;

class DeclarationManager
{
    public function sync(Post $post, array $declarations, User $actor): void
    {
        $post->getConnection()->transaction(function () use ($post, $declarations, $actor) {
            $post->creatorDeclarations()->delete();

            foreach ($declarations as $declaration) {
                $model = new CreatorDeclaration();
                $model->actor_id = $actor->id;
                $model->declaration_key = $declaration['key'];
                $model->source = $post->user_id === $actor->id ? 'creator' : 'moderator';
                $metadata = array_filter([
                    'details' => $declaration['details'],
                    'title' => $declaration['title'],
                ], fn (string $value) => $value !== '');
                $model->metadata = $metadata === [] ? null : $metadata;

                $post->creatorDeclarations()->save($model);
            }

            $post->unsetRelation('creatorDeclarations');
        });
    }
}
