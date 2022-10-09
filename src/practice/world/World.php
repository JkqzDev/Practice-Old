<?php

declare(strict_types=1);

namespace practice\world;

use Closure;
use pocketmine\math\Vector3;
use practice\Practice;
use practice\world\async\WorldCopyAsync;

class World {

    public function __construct(
        private string $name,
        private Vector3 $firstPosition,
        private Vector3 $secondPosition,
        private array $modes = [],
        private bool $copy = false,
        private ?Vector3 $firstPortal = null,
        private ?Vector3 $secondPortal = null
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

    public function getFirstPosition(): Vector3 {
        return $this->firstPosition;
    }

    public function getSecondPosition(): Vector3 {
        return $this->secondPosition;
    }

    public function isMode(string $mode): bool {
        return in_array($mode, $this->modes);
    }

    public function getFirstPortal(): ?Vector3 {
        return $this->firstPortal;
    }

    public function getSecondPortal(): ?Vector3 {
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
    
    static public function deserializeData(array $data): array {
        $storage = [
            'modes' => $data['modes'],
            'firstPosition' => new Vector3(
                floatval($data['firstPosition']['x']),
                floatval($data['firstPosition']['y']),
                floatval($data['firstPosition']['z'])
            ),
            'secondPosition' => new Vector3(
                floatval($data['secondPosition']['x']),
                floatval($data['secondPosition']['y']),
                floatval($data['secondPosition']['z'])
            ),
            'firstPortal' => null,
            'secondPortal' => null
        ];
        
        if ($data['firstPortal'] !== null && $data['secondPortal'] !== null) {
            $storage['firstPortal'] = new Vector3(
                floatval($data['firstPortal']['x']),
                floatval($data['firstPortal']['y']),
                floatval($data['firstPortal']['z'])
            );
            $storage['secondPortal'] = new Vector3(
                floatval($data['secondPortal']['x']),
                floatval($data['secondPortal']['y']),
                floatval($data['secondPortal']['z'])
            );
        }
        return $storage;
    }
}