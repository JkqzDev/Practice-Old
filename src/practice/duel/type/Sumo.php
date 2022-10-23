<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\block\Water;
use practice\duel\Duel;

class Sumo extends Duel {
    
    public function update(): void {
        parent::update();
        
        if ($this->status === self::RUNNING) {
            $firstSession = $this->firstSession;
            $secondSession = $this->secondSession;
            
            $firstPlayer = $firstSession->getPlayer();
            $secondPlayer = $secondSession->getPlayer();
            
            if ($firstPlayer !== null && $secondPlayer !== null) {
                if ($firstPlayer->getPosition()->getWorld()->getBlock($firstPlayer->getPosition()) instanceof Water || $firstPlayer->getPosition()->getY() < 0) {
                    $this->finish($firstPlayer);
                    return;
                } elseif ($secondPlayer->getPosition()->getWorld()->getBlock($secondPlayer->getPosition()) instanceof Water || $secondPlayer->getPosition()->getY() < 0) {
                    $this->finish($secondPlayer);
                    return;
                }
            }
        }
    }
}