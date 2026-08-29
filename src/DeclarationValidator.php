<?php

namespace FFans\CreatorDeclarations;

use Flarum\Foundation\ValidationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeclarationValidator
{
    private const SOURCE_KEYS = ['original', 'repost', 'reference'];

    private const URL_SOURCE_KEYS = ['repost', 'reference'];

    /** @var DeclarationRegistry */
    protected $registry;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(DeclarationRegistry $registry, TranslatorInterface $translator)
    {
        $this->registry = $registry;
        $this->translator = $translator;
    }

    public function validate(array $input, bool $required = false, array $preservedKeys = []): array
    {
        if ($required && count($input) === 0) {
            $this->fail('required');
        }

        if (count($input) > $this->registry->maxSelections()) {
            $this->fail('too_many');
        }

        $enabled = $this->registry->enabledKeys();
        $normalized = [];

        foreach ($input as $item) {
            if (! is_array($item) || ! isset($item['key']) || ! is_string($item['key'])) {
                $this->fail('invalid_payload');
            }

            $key = $item['key'];

            if (! in_array($key, DeclarationRegistry::KEYS, true) || isset($normalized[$key])) {
                $this->fail('invalid_selection');
            }

            if (! in_array($key, $enabled, true) && ! in_array($key, $preservedKeys, true)) {
                $this->fail('invalid_selection');
            }

            $details = trim((string) ($item['details'] ?? ''));
            $title = trim((string) ($item['title'] ?? ''));

            if (mb_strlen($details) > 500) {
                $this->fail('details_too_long');
            }

            if (mb_strlen($title) > 100) {
                $this->fail('title_too_long');
            }

            $scheme = strtolower((string) parse_url($details, PHP_URL_SCHEME));

            if (in_array($key, self::URL_SOURCE_KEYS, true) && ($details === '' || filter_var($details, FILTER_VALIDATE_URL) === false || ! in_array($scheme, ['http', 'https'], true))) {
                $this->fail('source_url_required');
            }

            $normalized[$key] = [
                'key' => $key,
                'details' => $details,
                'title' => $title,
            ];
        }

        if (count(array_intersect(array_keys($normalized), self::SOURCE_KEYS)) > 1) {
            $this->fail('source_conflict');
        }

        return array_values($normalized);
    }

    protected function fail(string $key): void
    {
        throw new ValidationException([
            'creatorDeclarationData' => $this->translator->trans("ffans-creator-declarations.lib.validation.$key"),
        ]);
    }
}
