<?php

declare(strict_types=1);

namespace practice\session\setting;

trait SettingTrait {
    
    private array $settings = [];

    public function getSettings(): array {
        return $this->settings;
    }
    
    public function getSetting(string $name): ?Setting {
        return $this->settings[$name] ?? null;
    }
    
    public function setSettings(array $settings): void {
        $this->settings = $settings;
    }
}