<?php

namespace FFans\CreatorDeclarations\Tests\Support;

use Flarum\Settings\SettingsRepositoryInterface;

class ArraySettingsRepository implements SettingsRepositoryInterface
{
    public $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function all(): array
    {
        return $this->values;
    }

    public function get($key, $default = null)
    {
        return $this->values[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    public function delete($keyLike)
    {
        unset($this->values[$keyLike]);
    }
}
