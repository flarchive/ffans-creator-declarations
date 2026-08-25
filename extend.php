<?php

use Flarum\Api\Controller\CreateDiscussionController;
use Flarum\Api\Controller\CreatePostController;
use Flarum\Api\Controller\ListPostsController;
use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Api\Controller\ShowPostController;
use Flarum\Api\Controller\UpdatePostController;
use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\Post\Event\Saving;
use FFans\CreatorDeclarations\Api\CreatorDeclarationSerializer;
use FFans\CreatorDeclarations\Api\PostAttributes;
use FFans\CreatorDeclarations\CreatorDeclaration;
use FFans\CreatorDeclarations\Listener\SaveCreatorDeclarations;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(Post::class))
        ->hasMany('creatorDeclarations', CreatorDeclaration::class, 'post_id'),

    (new Extend\Event())
        ->listen(Saving::class, SaveCreatorDeclarations::class),

    (new Extend\ApiSerializer(BasicPostSerializer::class))
        ->attribute('canEditCreatorDeclarations', PostAttributes::class)
        ->hasMany('creatorDeclarations', CreatorDeclarationSerializer::class),

    (new Extend\ApiController(CreateDiscussionController::class))
        ->addInclude('firstPost.creatorDeclarations'),

    (new Extend\ApiController(CreatePostController::class))
        ->addInclude('creatorDeclarations'),

    (new Extend\ApiController(UpdatePostController::class))
        ->addInclude('creatorDeclarations'),

    (new Extend\ApiController(ListPostsController::class))
        ->addInclude('creatorDeclarations'),

    (new Extend\ApiController(ShowDiscussionController::class))
        ->addInclude('posts.creatorDeclarations'),

    (new Extend\ApiController(ShowPostController::class))
        ->addOptionalInclude('creatorDeclarations'),

    (new Extend\Settings())
        ->default('ffans-creator-declarations.required_discussion', '0')
        ->default('ffans-creator-declarations.required_reply', '0')
        ->default('ffans-creator-declarations.max', '5')
        ->default('ffans-creator-declarations.order', 'original,repost,reference,ai_generated,fictional,personal_opinion,professional,sensitive,self_promotion,sponsored')
        ->default('ffans-creator-declarations.show_in_user_post_lists', '0')
        ->default('ffans-creator-declarations.allow_edit_own', 'reply')
        ->default('ffans-creator-declarations.enabled.original', '1')
        ->default('ffans-creator-declarations.enabled.repost', '1')
        ->default('ffans-creator-declarations.enabled.reference', '1')
        ->default('ffans-creator-declarations.enabled.ai_generated', '1')
        ->default('ffans-creator-declarations.enabled.fictional', '1')
        ->default('ffans-creator-declarations.enabled.personal_opinion', '1')
        ->default('ffans-creator-declarations.enabled.professional', '1')
        ->default('ffans-creator-declarations.enabled.sensitive', '1')
        ->default('ffans-creator-declarations.enabled.self_promotion', '1')
        ->default('ffans-creator-declarations.enabled.sponsored', '1')
        ->serializeToForum('creatorDeclarationOriginalEnabled', 'ffans-creator-declarations.enabled.original', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationRepostEnabled', 'ffans-creator-declarations.enabled.repost', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationReferenceEnabled', 'ffans-creator-declarations.enabled.reference', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationAiGeneratedEnabled', 'ffans-creator-declarations.enabled.ai_generated', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationFictionalEnabled', 'ffans-creator-declarations.enabled.fictional', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationPersonalOpinionEnabled', 'ffans-creator-declarations.enabled.personal_opinion', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationProfessionalEnabled', 'ffans-creator-declarations.enabled.professional', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationSensitiveEnabled', 'ffans-creator-declarations.enabled.sensitive', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationSelfPromotionEnabled', 'ffans-creator-declarations.enabled.self_promotion', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationSponsoredEnabled', 'ffans-creator-declarations.enabled.sponsored', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationShowInUserPostLists', 'ffans-creator-declarations.show_in_user_post_lists', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationOrder', 'ffans-creator-declarations.order')
        ->serializeToForum('creatorDeclarationsRequiredForDiscussion', 'ffans-creator-declarations.required_discussion', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationsRequiredForReply', 'ffans-creator-declarations.required_reply', function ($value) { return $value === '1'; })
        ->serializeToForum('creatorDeclarationsMax', 'ffans-creator-declarations.max', function ($value) { return (int) $value; }),
];
