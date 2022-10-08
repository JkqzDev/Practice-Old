<?php

declare(strict_types=1);

namespace practice\world;

use Closure;
use pocketmine\world\Position;
use practice\Practice;
use practice\world\async\WorldCopyAsync;

class World {

    public function __construct(
        private string $name,
        private Position $firstPosition,
        private Position $secondPosition,
        private array $modes = [],
        private bool $copy = false,
        private ?Position $firstPortal = null,
        private ?Position $secondPortal = null
    ) {
        if ($copy) {
            Practice::getInstance()->getServer()->getAsyncPool()->submitTask(new WorldCopyAsync(
                $name,
                Practice::getInstance()->getServer()->getDataPath() . 'worlds',
                $this->name,
                Practice::getInstance()->getDataFolder() . 'worlds'
            ));
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getFirstPosition(): Position {
        return $this->firstPosition;
    }

    public function getSecondPosition(): Position {
        return $this->secondPosition;
    }

    public function isMode(string $mode): bool {
        return in_array($mode, $this->modes);
    }

    public function getFirstPortal(): ?Position {
        return $this->firstPortal;
    }

    public function getSecondPortal(): ?Position {
        return $this->secondPortal;
    }

    public function copyWorld(string $newName, string $newDirectory, ?Closure $callback = null): void {
        Practice::getInstance()->getServer()->getAsyncPool()->submitTask(new WorldCopyAsync(
            $this->name,
            Practice::getInstance()->getDataFolder() . 'worlds',
            $newName,
            $newDirectory,
            $callback
        ));
    }
}