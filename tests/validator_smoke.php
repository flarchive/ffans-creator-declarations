<?php

require __DIR__.'/../vendor/autoload.php';

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Foundation\ValidationException;
use Flarum\Locale\Translator;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use FFans\CreatorDeclarations\DeclarationRegistry;
use FFans\CreatorDeclarations\DeclarationValidator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$settings = new class implements SettingsRepositoryInterface {
    public array $values = [];

    public function all(): array
    {
        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function delete(string $keyLike): void
    {
        unset($this->values[$keyLike]);
    }
};

$registry = new DeclarationRegistry($settings);
$translator = new Translator('en');
$translator->addLoader('yaml', new YamlFileLoader());
$translator->addResource('yaml', __DIR__.'/../locale/en.yml', 'en');
$validator = new DeclarationValidator($registry, $translator);

$valid = $validator->validate([
    ['key' => 'original'],
    ['key' => 'ai_generated', 'details' => 'Local model'],
    ['key' => 'personal_opinion'],
]);

assert(count($valid) === 3);
assert($valid[1]['details'] === 'Local model');
assert($valid[2]['key'] === 'personal_opinion');

$expectFailure = function (array $payload, bool $required = false, ?string $expectedMessage = null) use ($validator): void {
    try {
        $validator->validate($payload, $required);
    } catch (ValidationException $exception) {
        if ($expectedMessage !== null) {
            assert($exception->getRelationships()['creatorDeclarationData'] === $expectedMessage);
        }

        return;
    }

    throw new RuntimeException('Expected declaration validation to fail.');
};

$expectFailure([
    ['key' => 'original'],
    ['key' => 'repost', 'details' => 'https://example.com/source'],
]);
$expectFailure([
    ['key' => 'repost', 'details' => 'not-a-url'],
]);
$expectFailure([
    ['key' => 'unknown'],
]);

$settings->set('ffans-creator-declarations.enabled.personal_opinion', '0');

$expectFailure([
    ['key' => 'personal_opinion'],
]);

$frozen = $validator->validate([
    ['key' => 'personal_opinion'],
], false, ['personal_opinion']);

assert($frozen[0]['key'] === 'personal_opinion');

$discussion = new Discussion();
$discussion->setRawAttributes(['last_post_number' => 2]);

$post = new Post();
$post->setRawAttributes([
    'number' => 2,
    'created_at' => Carbon::now()->subMinutes(5),
]);
$post->setRelation('discussion', $discussion);

$settings->set('ffans-creator-declarations.allow_edit_own', 'reply');
assert($registry->allowsEditingOwn($post));

$discussion->last_post_number = 3;
assert(! $registry->allowsEditingOwn($post));

$settings->set('ffans-creator-declarations.allow_edit_own', '10');
assert($registry->allowsEditingOwn($post));

$post->setRawAttributes([
    'number' => 2,
    'created_at' => Carbon::now()->subMinutes(11),
]);
assert(! $registry->allowsEditingOwn($post));

$settings->set('ffans-creator-declarations.allow_edit_own', '-1');
assert($registry->allowsEditingOwn($post));

assert($validator->validate([]) === []);
$expectFailure([], true, 'At least one creator declaration is required.');

echo "validator-smoke-ok\n";
