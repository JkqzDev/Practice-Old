<?php

declare(strict_types=1);

namespace practice\session\setting\gameplay;

use practice\session\setting\Setting;

class GameplaySetting extends Setting {

    public function __construct(string $name, bool $value = true) {
        parent::__construct($name, $value);
    }

    public function isEnabled(): bool {
        return $this->value;
    }

    public function setEnabled(bool $value): void {
        $this->value = $value;
    }
}