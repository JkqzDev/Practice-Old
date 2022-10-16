<?php

declare(strict_types=1);

namespace practice\session\setting;

use practice\session\setting\display\CPSCounter;
use practice\session\setting\display\Scoreboard;
use practice\session\setting\gameplay\AutoRespawn;

class Setting {
    
    static public function create(): array {
        return [
            'scoreboard' => new Scoreboard,
            'cps_counter' => new CPSCounter,
            'auto_respawn' => new AutoRespawn
        ];
    }
    
    public function __construct(
        protected string $name,
        protected mixed $value
    ) {}
    
    public function getName(): string {
        return $this->name;
    }
    
    public function serializeData(): array {
        return [
            'value' => $this->value
        ];
    }
}