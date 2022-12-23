<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\block\Water;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use practice\duel\Duel;

class Sumo extends Duel {

    public function handleDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }

        if (!$this->isRunning()) {
            $event->cancel();
            return;
        }
        $player->setHealth($player->getMaxHealth());
    }

    public function update(): void {
        parent::update();

        if ($this->status === self::RUNNING) {
            $firstSession = $this->firstSession;
            $secondSession = $this->secondSession;

            $firstPlayer = $firstSession->getPlayer();
            $secondPlayer = $secondSession->getPlayer();

            if ($firstPlayer !== null && $secondPlayer !== null) {
                if ($firstPlayer->getPosition()->getY() < 0 || $firstPlayer->getPosition()->getWorld()->getBlock($firstPlayer->getPosition()) instanceof Water) {
                    $this->finish($firstPlayer);
                    return;
                }

                if ($secondPlayer->getPosition()->getY() < 0 || $secondPlayer->getPosition()->getWorld()->getBlock($secondPlayer->getPosition()) instanceof Water) {
                    $this->finish($secondPlayer);
                }
            }
        }
    }
}