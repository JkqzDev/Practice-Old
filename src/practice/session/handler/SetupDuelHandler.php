<?php

declare(strict_types=1);

namespace practice\session\handler;

use pocketmine\world\Position;

final class SetupDuelHandler {
    
    public const SETUP_SPAWN = 0;
    public const SETUP_PORTAL = 1;
    
    public function __construct(
        private int $mode = self::SETUP_SPAWN,
        private array $modes = [],
        private bool $withPortal = false,
        private ?Position $firstPosition = null,
        private ?Position $secondPosition = null,
        private ?Position $firstPortal = null,
        private ?Position $secondPortal = null,
        private ?string $world = null
    ) {}
    
    public function getMode(): int {
        return $this->mode;
    }
    
    public function addMode(string $mode): void {
        $this->modes[] = $mode;
    }

    public function setWithPortal(bool $withPortal): void {
        $this->withPortal = $withPortal;
    }

    public function setFirstPosition(Position $position): void {
        $this->firstPosition = $position;
    }

    public function setSecondPosition(Position $position): void {
        $this->secondPosition = $position;
    }

    public function setFirstPortal(Position $position): void {
        $this->firstPortal = $position;
    }

    public function setSecondPortal(Position $position): void {
        $this->secondPortal = $position;
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }
}