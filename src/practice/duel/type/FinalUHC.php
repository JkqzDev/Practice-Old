<?php

declare(strict_types=1);

namespace practice\duel\type;

use practice\duel\Duel;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerMoveEvent;

class FinalUHC extends Duel {

    private int $size = 24;
    private int $x, $z;

    public function handleMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();

        if (!$from->equals($to)) {
            if (!$this->insideBorder($player)) {
                $this->teleportPlayer($player);
            }
        }
    }

    private function insideBorder(Player $player): bool {
        $position = $player->getPosition();

        if ($position->getFloorX() > ($this->x + $this->size) || $position->getFloorX() < ($this->x - $this->size) ||
            $position->getFloorZ() > ($this->z + $this->size) || $position->getFloorZ() < ($this->z - $this->size)) {
            return false;
        }
        return true;
    }

    private function teleportPlayer(Player $player): void {
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

    protected function init(): void {
        $this->x = $this->world->getSafeSpawn()->getFloorX();
        $this->z = $this->world->getSafeSpawn()->getFloorZ();
    }
}