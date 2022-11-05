<?php

declare(strict_types=1);

namespace practice\party\duel\type;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use practice\party\duel\Duel;

final class FinalUHC extends Duel {

    private int $size = 24;
    private int $x, $z;

    public function handleMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();

        if (!$from->equals($to) && !$this->insideBorder($player)) {
            $this->teleportPlayer($player);
        }
    }

    private function insideBorder(Player $player): bool {
        $position = $player->getPosition();

        return !($position->getFloorX() > ($this->x + $this->size) || $position->getFloorX() < ($this->x - $this->size) ||
            $position->getFloorZ() > ($this->z + $this->size) || $position->getFloorZ() < ($this->z - $this->size));
    }

    private function teleportPlayer(Player $player): void {
        $position = $player->getPosition();

        $outsideX = ($position->getFloorX() < $this->x ? $position->getFloorX() <= ($this->x - $this->size) : $position->getFloorX() >= ($this->x + $this->size));
        $outsideZ = ($position->getFloorZ() < $this->z ? $position->getFloorZ() <= ($this->z - $this->size) : $position->getFloorZ() >= ($this->z + $this->size));
        $teleportDistance = 1.6;
        $newPosition = $position;

        if ($outsideX) {
            $newPosition->x = ($position->getFloorX() < $this->x ? ($this->x - $this->size + $teleportDistance) : ($this->x + $this->size - $teleportDistance));
        } else {
            $newPosition->x = $position->x;
        }

        if ($outsideZ) {
            $newPosition->z = ($position->getFloorZ() < $this->z ? ($this->z - $this->size + $teleportDistance) : ($this->z + $this->size - $teleportDistance));
        } else {
            $newPosition->z = $position->z;
        }

        $newPosition->y = $this->world->getHighestBlockAt($newPosition->getFloorX(), $newPosition->getFloorZ());

        $player->teleport($newPosition->add(0, 1, 0));
    }

    protected function init(): void {
        $this->x = $this->world->getSafeSpawn()->getFloorX();
        $this->z = $this->world->getSafeSpawn()->getFloorZ();
    }
}