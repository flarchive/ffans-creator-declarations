<?php

namespace FFans\CreatorDeclarations\Tests\integration;

use Flarum\Testing\integration\TestCase;
use FFans\CreatorDeclarations\CreatorDeclaration;

class CreatorDeclarationsApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ffans-creator-declarations');
    }

    /** @test */
    public function creating_a_discussion_stores_and_includes_first_post_declarations(): void
    {
        $response = $this->send($this->request('POST', '/api/discussions', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'discussions',
                    'attributes' => [
                        'title' => 'Test discussion',
                        'content' => 'Test body',
                        'creatorDeclarationData' => [
                            ['key' => 'original'],
                            ['key' => 'ai_generated', 'details' => 'Local model'],
                        ],
                    ],
                ],
            ],
        ]));

        $document = json_decode((string) $response->getBody(), true);

        $this->assertSame(201, $response->getStatusCode(), json_encode($document));
        $this->assertCount(2, CreatorDeclaration::all());
        $this->assertEqualsCanonicalizing(
            ['original', 'ai_generated'],
            collect($document['included'])->where('type', 'creator-declarations')->pluck('attributes.key')->all()
        );
    }

    /** @test */
    public function showing_a_discussion_includes_declarations_for_its_posts(): void
    {
        $createResponse = $this->send($this->request('POST', '/api/discussions', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'discussions',
                    'attributes' => [
                        'title' => 'Discussion with declarations',
                        'content' => 'Test body',
                        'creatorDeclarationData' => [
                            ['key' => 'original'],
                        ],
                    ],
                ],
            ],
        ]));
        $created = json_decode((string) $createResponse->getBody(), true);

        $this->assertSame(201, $createResponse->getStatusCode(), json_encode($created));

        $discussionId = $created['data']['id'];
        $response = $this->send($this->request('GET', "/api/discussions/$discussionId"));
        $document = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode(), json_encode($document));

        $post = collect($document['included'])->firstWhere('type', 'posts');
        $declarations = collect($document['included'])->where('type', 'creator-declarations');

        $this->assertNotNull($post);
        $this->assertSame(
            $declarations->pluck('id')->all(),
            collect($post['relationships']['creatorDeclarations']['data'])->pluck('id')->all()
        );
        $this->assertSame(['original'], $declarations->pluck('attributes.key')->all());
    }

    /** @test */
    public function required_discussion_declarations_are_enforced(): void
    {
        $this->setting('ffans-creator-declarations.required_discussion', '1');

        $response = $this->send($this->request('POST', '/api/discussions', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'discussions',
                    'attributes' => [
                        'title' => 'Missing declaration',
                        'content' => 'Test body',
                    ],
                ],
            ],
        ]));

        $document = json_decode((string) $response->getBody(), true);

        $this->assertSame(422, $response->getStatusCode(), json_encode($document));
        $this->assertSame('/data/attributes/creatorDeclarationData', $document['errors'][0]['source']['pointer']);
    }

    /** @test */
    public function invalid_source_urls_are_rejected_by_the_api(): void
    {
        $response = $this->send($this->request('POST', '/api/discussions', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'discussions',
                    'attributes' => [
                        'title' => 'Invalid source',
                        'content' => 'Test body',
                        'creatorDeclarationData' => [
                            ['key' => 'repost', 'details' => 'not-a-url'],
                        ],
                    ],
                ],
            ],
        ]));

        $document = json_decode((string) $response->getBody(), true);

        $this->assertSame(422, $response->getStatusCode(), json_encode($document));
        $this->assertSame('/data/attributes/creatorDeclarationData', $document['errors'][0]['source']['pointer']);
        $this->assertCount(0, CreatorDeclaration::all());
    }

    /** @test */
    public function required_reply_declarations_are_enforced_without_affecting_the_first_post(): void
    {
        $this->setting('ffans-creator-declarations.required_reply', '1');
        $discussionId = $this->createDiscussion();

        $response = $this->send($this->request('POST', '/api/posts', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'posts',
                    'attributes' => ['content' => 'A reply without declarations'],
                    'relationships' => [
                        'discussion' => [
                            'data' => ['type' => 'discussions', 'id' => $discussionId],
                        ],
                    ],
                ],
            ],
        ]));
        $document = json_decode((string) $response->getBody(), true);

        $this->assertSame(422, $response->getStatusCode(), json_encode($document));
        $this->assertSame('/data/attributes/creatorDeclarationData', $document['errors'][0]['source']['pointer']);
    }

    /** @test */
    public function reply_declarations_can_be_created_then_replaced_through_the_api(): void
    {
        $discussionId = $this->createDiscussion();

        $createResponse = $this->send($this->request('POST', '/api/posts', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'posts',
                    'attributes' => [
                        'content' => 'A reply',
                        'creatorDeclarationData' => [
                            ['key' => 'sponsored', 'details' => 'Sponsor'],
                        ],
                    ],
                    'relationships' => [
                        'discussion' => [
                            'data' => ['type' => 'discussions', 'id' => $discussionId],
                        ],
                    ],
                ],
            ],
        ]));
        $created = json_decode((string) $createResponse->getBody(), true);

        $this->assertSame(201, $createResponse->getStatusCode(), json_encode($created));
        $this->assertTrue($created['data']['attributes']['canEditCreatorDeclarations']);
        $includedDeclarations = collect($created['included'])->where('type', 'creator-declarations');
        $this->assertSame('sponsored', $includedDeclarations->first()['attributes']['key']);

        $postId = $created['data']['id'];
        $updateResponse = $this->send($this->request('PATCH', "/api/posts/$postId", [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'posts',
                    'id' => $postId,
                    'attributes' => [
                        'creatorDeclarationData' => [
                            ['key' => 'ai_generated', 'details' => 'Local model'],
                        ],
                    ],
                ],
            ],
        ]));
        $updated = json_decode((string) $updateResponse->getBody(), true);

        $this->assertSame(200, $updateResponse->getStatusCode(), json_encode($updated));
        $this->assertSame('ai_generated', CreatorDeclaration::sole()->declaration_key);
        $this->assertSame(['details' => 'Local model'], CreatorDeclaration::sole()->metadata);
    }

    /** @test */
    public function forum_payload_exposes_the_effective_configuration(): void
    {
        $this->setting('ffans-creator-declarations.required_discussion', '1');
        $this->setting('ffans-creator-declarations.required_reply', '1');
        $this->setting('ffans-creator-declarations.max', '7');

        $response = $this->send($this->request('GET', '/api'));
        $document = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode(), json_encode($document));
        $this->assertTrue($document['data']['attributes']['creatorDeclarationsRequiredForDiscussion']);
        $this->assertTrue($document['data']['attributes']['creatorDeclarationsRequiredForReply']);
        $this->assertSame(7, $document['data']['attributes']['creatorDeclarationsMax']);
    }

    private function createDiscussion(): string
    {
        $response = $this->send($this->request('POST', '/api/discussions', [
            'authenticatedAs' => 1,
            'json' => [
                'data' => [
                    'type' => 'discussions',
                    'attributes' => [
                        'title' => 'Reply test discussion',
                        'content' => 'First post',
                    ],
                ],
            ],
        ]));

        $document = json_decode((string) $response->getBody(), true);
        $this->assertSame(201, $response->getStatusCode(), json_encode($document));

        return $document['data']['id'];
    }
}
