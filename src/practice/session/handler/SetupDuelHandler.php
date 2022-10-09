<?php

declare(strict_types=1);

namespace practice\session\handler;

use pocketmine\math\Vector3;

final class SetupDuelHandler {
    
    public const SETUP_SPAWN = 0;
    public const SETUP_PORTAL = 1;
    
    public function __construct(
        private int $mode = self::SETUP_SPAWN,
        private array $modes = [],
        private bool $withPortal = false,
        private ?Vector3 $firstPosition = null,
        private ?Vector3 $secondPosition = null,
        private ?Vector3 $firstPortal = null,
        private ?Vector3 $secondPortal = null,
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

    public function setFirstPosition(Vector3 $position): void {
        $this->firstPosition = $position;
    }

    public function setSecondPosition(Vector3 $position): void {
        $this->secondPosition = $position;
    }

    public function setFirstPortal(Vector3 $position): void {
        $this->firstPortal = $position;
    }

    public function setSecondPortal(Vector3 $position): void {
        $this->secondPortal = $position;
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }
}