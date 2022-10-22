<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\player\Player;
use practice\duel\Duel;

class FinalUHC extends Duel {
    
    private int $size = 24;
    private int $x, $z;
    
    protected function init(): void {
        $this->x = $this->world->getSafeSpawn()->getFloorX();
        $this->z = $this->world->getSafeSpawn()->getFloorZ();
    }
    
    public function insideBorder(Player $player): bool {
        $position = $player->getPosition();
        
        if ($position->getFloorX() > ($this->x + $this->size) || $position->getFloorX() < ($this->x - $this->size) ||
            $position->getFloorZ() > ($this->z + $this->size) || $position->getFloorZ() < ($this->z - $this->size)) {
            return false;
        }
        return true;
    }
    
    public function teleportPlayer(Player $player): void {
        $position = $player->getPosition();
        
        $outsideX = ($position->getFloorX() < $this->x ? $position->getFloorX() <= ($this->x - $this->size) : $position->getFloorX() >= ($this->x + $this->size));
        $outsideZ = ($position->getFloorZ() < $this->z ? $position->getFloorZ() <= ($this->z - $this->size) : $position->getFloorZ() >= ($this->z + $this->size));
        $teleportDistance = 1.6;
        $newPosition = $position;
        $newPosition->x = $outsideX ? ($position->getFloorX() < $this->x ? ($this->x - $this->size + $teleportDistance) : ($this->x + $this->size - $teleportDistance)) : $position->x;
        $newPosition->z = $outsideZ ? ($position->getFloorZ() < $this->z ? ($this->z - $this->size + $teleportDistance) : ($this->z + $this->size - $teleportDistance)) : $position->z;
        $newPosition->y = $this->world->getHighestBlockAt($newPosition->getFloorX(), $newPosition->getFloorZ());
        
        $player->teleport($newPosition->add(0, 1, 0));
    }
}