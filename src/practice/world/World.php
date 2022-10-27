<?php

declare(strict_types=1);

namespace practice\world;

use Closure;
use practice\Practice;
use pocketmine\world\Position;
use practice\world\async\WorldCopyAsync;

final class World {

    public function __construct(
        private string    $name,
        private Position  $firstPosition,
        private Position  $secondPosition,
        private array     $modes = [],
        bool              $copy = false,
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

    public static function deserializeData(array $data): array {
        $storage = [
            'modes' => $data['modes'],
            'firstPosition' => new Position(
                (float)$data['firstPosition']['x'],
                (float)$data['firstPosition']['y'],
                (float)$data['firstPosition']['z'],
                null
            ),
            'secondPosition' => new Position(
                (float)$data['secondPosition']['x'],
                (float)$data['secondPosition']['y'],
                (float)$data['secondPosition']['z'],
                null
            ),
            'firstPortal' => null,
            'secondPortal' => null
        ];

        if ($data['firstPortal'] !== null && $data['secondPortal'] !== null) {
            $storage['firstPortal'] = new Position(
                (float)$data['firstPortal']['x'],
                (float)$data['firstPortal']['y'],
                (float)$data['firstPortal']['z'],
                null
            );
            $storage['secondPortal'] = new Position(
                (float)$data['secondPortal']['x'],
                (float)$data['secondPortal']['y'],
                (float)$data['secondPortal']['z'],
                null
            );
        }
        return $storage;
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
        return in_array($mode, $this->modes, true);
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

    public function serializeData(): array {
        $firstPosition = $this->firstPosition;
        $secondPosition = $this->secondPosition;

        $firstPortal = $this->firstPortal;
        $secondPortal = $this->secondPortal;

        $data = [
            'modes' => $this->modes,
            'firstPosition' => [
                'x' => $firstPosition->getX(),
                'y' => $firstPosition->getY(),
                'z' => $firstPosition->getZ()
            ],
            'secondPosition' => [
                'x' => $secondPosition->getX(),
                'y' => $secondPosition->getY(),
                'z' => $secondPosition->getZ()
            ],
            'firstPortal' => null, // For bridge
            'secondPortal' => null // For bridge
        ];

        if ($firstPortal !== null && $secondPortal !== null) {
            $data['firstPortal'] = [
                'x' => $firstPortal->getX(),
                'y' => $firstPortal->getY(),
                'z' => $firstPortal->getZ()
            ];
            $data['secondPortal'] = [
                'x' => $secondPortal->getX(),
                'y' => $secondPortal->getY(),
                'z' => $secondPortal->getZ()
            ];
        }
        return $data;
    }
}