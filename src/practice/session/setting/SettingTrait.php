<?php

declare(strict_types=1);

namespace practice\session\setting;

use practice\session\setting\display\DisplaySetting;
use practice\session\setting\gameplay\GameplaySetting;

trait SettingTrait {

    private array $settings = [];

    public function getSettings(): array {
        return $this->settings;
    }

    public function setSettings(array $settings): void {
        $this->settings = $settings;
    }

    public function getSetting(string $name): Setting|GameplaySetting|DisplaySetting|null {
        return $this->settings[$name] ?? null;
    }
}