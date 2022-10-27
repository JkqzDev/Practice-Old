<?php

declare(strict_types=1);

namespace practice\session\setting;

use practice\session\setting\display\CPSCounter;
use practice\session\setting\display\Scoreboard;
use practice\session\setting\gameplay\AutoRespawn;

class Setting {

    public const SCOREBOARD = 'scoreboard';
    public const CPS_COUNTER = 'cps_counter';
    public const AUTO_RESPAWN = 'auto_respawn';

    public function __construct(
        protected string $name,
        protected mixed  $value
    ) {}

    public static function create(): array {
        return [
            self::SCOREBOARD => new Scoreboard,
            self::CPS_COUNTER => new CPSCounter,
            self::AUTO_RESPAWN => new AutoRespawn
        ];
    }

    public function getName(): string {
        return $this->name;
    }

    public function serializeData(): array {
        return [
            'value' => $this->value
        ];
    }
}