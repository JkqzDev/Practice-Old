<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\event\entity\EntityDamateByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use practice\duel\Duel;

class Boxing extends Duel {
    
    private int $fistHit = 0, $secondHit = 0;
    private int $firstCombo = 0, $secondCombo = 0;
    
    public function handleDamage(EntityDamageEvent $event): void {
        parent::handleDamage($event);
        
        if ($event->isCancelled()) {
            return;
        }
        
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            
            if ($damager instanceof Player) {
                $firstSession = $this->firstSession;
                $secondSession = $this->secondSession;
                
                if ($firstSession->getName() === $damager->getName()) {
                    $this->firstHit++;
                    $this->firstCombo++;
                    
                    $this->secondCombo = 0;
                } else {
                    $this->secondHit++;
                    $this->secondCombo++;
                    
                    $this->firstCombo = 0;
                }
            }
        }
    }
    
    public function scoreboard(Player $player): array {
        if ($this->status === self::RUNNING) {
            if ($this->isSpectator($player)) {
            }
        }
        return parent::scoreboard($player);
    }
    
    public function update(): void {
        parent::update();
        
        if ($this->status === self::RUNNING) {
            $firstSession = $this->firstSession;
            $secondSession = $this->secondSession;
        
            if ($this->firstHit >= 100) {
                $this->finish($secondSession->getPlayer());
            } elseif ($this->secondHit >= 100) {
                $this->finish($firstSession->getPlayer());
            )
        }
    }
}