<?php

declare(strict_types=1);

namespace practice\session\setting\display;

use practice\session\Session;

class CPSCounter extends DisplaySetting {

    public function __construct(
        private array $clicks = []
    ) {
        parent::__construct('CPS Counter');
    }

    public function execute(Session $session): void {}

    public function addClick(): void {
        array_unshift($this->clicks, microtime(true));

        if (count($this->clicks) >= 100) {
            array_pop($this->clicks);
        }
    }

    public function getCPS(float $deltaTime = 1.0, int $roundPrecision = 1): float {
        if (empty($this->clicks)) {
            return 0.0;
        }
        $now = microtime(true);

        return round(count(array_filter($this->clicks, static function(float $time) use ($deltaTime, $now): bool {
                return ($now - $time) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }
}