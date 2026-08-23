<?php

namespace FFans\CreatorDeclarations;

use Flarum\Foundation\ValidationException;
use Flarum\Locale\TranslatorInterface;

class DeclarationValidator
{
    public function __construct(
        protected DeclarationRegistry $registry,
        protected TranslatorInterface $translator
    ) {
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

            if (mb_strlen($details) > 500) {
                $this->fail('details_too_long');
            }

            $scheme = strtolower((string) parse_url($details, PHP_URL_SCHEME));

            if ($key === 'repost' && ($details === '' || filter_var($details, FILTER_VALIDATE_URL) === false || ! in_array($scheme, ['http', 'https'], true))) {
                $this->fail('repost_url_required');
            }

            $normalized[$key] = [
                'key' => $key,
                'details' => $details,
            ];
        }

        if (isset($normalized['original'], $normalized['repost'])) {
            $this->fail('source_conflict');
        }

        return array_values($normalized);
    }

    protected function fail(string $key): never
    {
        throw new ValidationException([], [
            'creatorDeclarationData' => $this->translator->trans("ffans-creator-declarations.lib.validation.$key"),
        ]);
    }
}
