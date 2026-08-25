<?php

namespace FFans\CreatorDeclarations\Tests\unit;

use Flarum\Foundation\ValidationException;
use FFans\CreatorDeclarations\DeclarationRegistry;
use FFans\CreatorDeclarations\DeclarationValidator;
use FFans\CreatorDeclarations\Tests\Support\ArraySettingsRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeclarationValidatorTest extends TestCase
{
    /** @test */
    public function it_normalizes_valid_input(): void
    {
        $result = $this->validator()->validate([
            ['key' => 'original', 'details' => '  detail  ', 'title' => '  title  '],
            ['key' => 'ai_generated'],
        ]);

        $this->assertSame([
            ['key' => 'original', 'details' => 'detail', 'title' => 'title'],
            ['key' => 'ai_generated', 'details' => '', 'title' => ''],
        ], $result);
    }

    /** @test */
    public function reposts_and_references_accept_only_http_urls(): void
    {
        foreach (['repost', 'reference'] as $key) {
            $result = $this->validator()->validate([
                ['key' => $key, 'details' => 'https://example.com/source'],
            ]);

            $this->assertSame('https://example.com/source', $result[0]['details']);
        }
    }

    /** @test */
    public function a_disabled_existing_selection_can_be_preserved_during_editing(): void
    {
        $validator = $this->validator([
            'ffans-creator-declarations.enabled.sensitive' => '0',
        ]);

        $this->expectValidationFailure($validator, [['key' => 'sensitive']], 'invalid_selection');
        $this->assertSame('sensitive', $validator->validate([['key' => 'sensitive']], false, ['sensitive'])[0]['key']);
    }

    /**
     * @test
     * @dataProvider invalidPayloads
     */
    public function it_rejects_invalid_input(array $payload, string $error, bool $required = false, array $settings = []): void
    {
        $this->expectValidationFailure($this->validator($settings), $payload, $error, $required);
    }

    public static function invalidPayloads(): array
    {
        return [
            'required empty selection' => [[], 'required', true],
            'non-array item' => [['original'], 'invalid_payload'],
            'missing key' => [[['details' => 'x']], 'invalid_payload'],
            'unknown key' => [[['key' => 'unknown']], 'invalid_selection'],
            'duplicate key' => [[['key' => 'original'], ['key' => 'original']], 'invalid_selection'],
            'too many' => [[['key' => 'original'], ['key' => 'ai_generated']], 'too_many', false, ['ffans-creator-declarations.max' => '1']],
            'details too long' => [[['key' => 'ai_generated', 'details' => 'a'.str_repeat('界', 500)]], 'details_too_long'],
            'title too long' => [[['key' => 'ai_generated', 'title' => str_repeat('a', 101)]], 'title_too_long'],
            'missing source URL' => [[['key' => 'repost']], 'source_url_required'],
            'non-http source URL' => [[['key' => 'reference', 'details' => 'ftp://example.com']], 'source_url_required'],
            'conflicting source declarations' => [[['key' => 'original'], ['key' => 'repost', 'details' => 'https://example.com']], 'source_conflict'],
        ];
    }

    private function validator(array $settings = []): DeclarationValidator
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(function (string $key) {
            return $key;
        });

        return new DeclarationValidator(
            new DeclarationRegistry(new ArraySettingsRepository($settings)),
            $translator
        );
    }

    private function expectValidationFailure(DeclarationValidator $validator, array $payload, string $error, bool $required = false): void
    {
        try {
            $validator->validate($payload, $required);
            $this->fail('Expected declaration validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                "ffans-creator-declarations.lib.validation.$error",
                $exception->getAttributes()['creatorDeclarationData']
            );
        }
    }
}
