<?php

use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Extend;
use Flarum\Post\Post;
use FFans\CreatorDeclarations\Api\DiscussionResourceFields;
use FFans\CreatorDeclarations\Api\ForumResourceFields;
use FFans\CreatorDeclarations\Api\PostResourceFields;
use FFans\CreatorDeclarations\Api\Resource\CreatorDeclarationResource;
use FFans\CreatorDeclarations\CreatorDeclaration;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->jsDirectory(__DIR__.'/js/dist/forum')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(Post::class))
        ->hasMany('creatorDeclarations', CreatorDeclaration::class, 'post_id'),

    new Extend\ApiResource(CreatorDeclarationResource::class),

    (new Extend\ApiResource(Resource\DiscussionResource::class))
        ->fields(DiscussionResourceFields::class)
        ->endpoint(
            [Endpoint\Create::class, Endpoint\Show::class],
            fn ($endpoint) => $endpoint->addDefaultInclude(['firstPost.creatorDeclarations'])
        ),

    (new Extend\ApiResource(Resource\PostResource::class))
        ->fields(PostResourceFields::class)
        ->endpoint(
            [Endpoint\Create::class, Endpoint\Update::class, Endpoint\Show::class, Endpoint\Index::class],
            fn ($endpoint) => $endpoint->addDefaultInclude(['creatorDeclarations'])
        ),

    (new Extend\ApiResource(Resource\ForumResource::class))
        ->fields(ForumResourceFields::class),

    (new Extend\Settings())
        ->default('ffans-creator-declarations.required_discussion', '0')
        ->default('ffans-creator-declarations.required_reply', '0')
        ->default('ffans-creator-declarations.max', '5')
        ->default('ffans-creator-declarations.order', 'original,repost,ai_generated,fictional,personal_opinion,professional,sensitive,self_promotion,sponsored')
        ->default('ffans-creator-declarations.show_original_in_post_header', '0')
        ->default('ffans-creator-declarations.show_in_user_post_lists', '0')
        ->default('ffans-creator-declarations.allow_edit_own', 'reply')
        ->default('ffans-creator-declarations.enabled.original', '1')
        ->default('ffans-creator-declarations.enabled.repost', '1')
        ->default('ffans-creator-declarations.enabled.ai_generated', '1')
        ->default('ffans-creator-declarations.enabled.fictional', '1')
        ->default('ffans-creator-declarations.enabled.personal_opinion', '1')
        ->default('ffans-creator-declarations.enabled.professional', '1')
        ->default('ffans-creator-declarations.enabled.sensitive', '1')
        ->default('ffans-creator-declarations.enabled.self_promotion', '1')
        ->default('ffans-creator-declarations.enabled.sponsored', '1')
        ->serializeToForum('creatorDeclarationOriginalEnabled', 'ffans-creator-declarations.enabled.original', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationRepostEnabled', 'ffans-creator-declarations.enabled.repost', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationAiGeneratedEnabled', 'ffans-creator-declarations.enabled.ai_generated', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationFictionalEnabled', 'ffans-creator-declarations.enabled.fictional', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationPersonalOpinionEnabled', 'ffans-creator-declarations.enabled.personal_opinion', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationProfessionalEnabled', 'ffans-creator-declarations.enabled.professional', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationSensitiveEnabled', 'ffans-creator-declarations.enabled.sensitive', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationSelfPromotionEnabled', 'ffans-creator-declarations.enabled.self_promotion', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationSponsoredEnabled', 'ffans-creator-declarations.enabled.sponsored', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationShowOriginalInPostHeader', 'ffans-creator-declarations.show_original_in_post_header', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationShowInUserPostLists', 'ffans-creator-declarations.show_in_user_post_lists', fn ($value) => $value === '1')
        ->serializeToForum('creatorDeclarationOrder', 'ffans-creator-declarations.order'),
];
