<?php

namespace FFans\CreatorDeclarations;

use Flarum\Database\AbstractModel;
use Flarum\Post\Post;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $post_id
 * @property int|null $actor_id
 * @property string $declaration_key
 * @property string $source
 * @property array|null $metadata
 */
class CreatorDeclaration extends AbstractModel
{
    protected $table = 'ffans_post_creator_declarations';

    public $timestamps = true;

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
