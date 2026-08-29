<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if ($schema->hasTable('ffans_post_creator_declarations')) {
            return;
        }

        $schema->create('ffans_post_creator_declarations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('declaration_key', 64);
            $table->string('source', 16)->default('creator');
            $table->json('metadata')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->unique(['post_id', 'declaration_key'], 'ffans_creator_declarations_post_key_unique');
            $table->index('declaration_key');
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
        });
    },
    'down' => function (Builder $schema) {
        $schema->dropIfExists('ffans_post_creator_declarations');
        $schema->dropIfExists('ffans_discussion_creator_declarations');
    },
];
