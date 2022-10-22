<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use practice\duel\DuelFactory;
use practice\duel\Duel;

class Boxing extends Duel {
    
    private int $firstHit = 0, $secondHit = 0;
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

                    if ($this->firstHit >= 100) {
                        $this->finish($secondSession->getPlayer());
                    }
                } else {
                    $this->secondHit++;
                    $this->secondCombo++;
                    
                    $this->firstCombo = 0;

                    if ($this->secondHit >= 100) {
                        $this->finish($firstSession->getPlayer());
                    }
                }
            }
        }
    }
    
    public function scoreboard(Player $player): array {
        if ($this->status === self::RUNNING) {
            $firstSession = $this->firstSession;
            $secondSession = $this->secondSession;
            
            if ($this->isSpectator($player)) {
                return [
                    ' &fKit: &b' . DuelFactory::getName($this->typeId),
                    ' &fType: &b' . ($this->ranked ? 'Ranked' : 'Unranked'),
                    ' &r&r',
                    ' &fDuration: &b' . gmdate('i:s', $this->running),
                    ' &fSpectators: &b' . count($this->spectators),
                    ' &fHits: &a' . $this->firstHit . ' &7| &c' . $this->secondHit
                ];
            }
            $opponent = $this->getOpponent($player);
            $isFirst = $firstSession->getName() === $player->getName();
            
            $playerHits = $this->firstHit;
            $playerCombo = $this->firstCombo;
            
            $opponentHits = $this->secondHit;
            $opponentCombo = $this->secondCombo;
            
            $hits = $this->firstHit - $this->secondHit;
            
            if (!$isFirst) {
                $hits = $this->secondHit - $this->firstHit;
                
                $playerCombo = $this->secondCombo;
                $opponentCombo = $this->firstCombo;
                
                $playerHits = $this->secondHit;
                $opponentHits = $this->firstHit;
            }
            
            return [
                ' &fFighting: &b' . $opponent->getName(),
                ' &r&r&r',
                ' &bHits: ' . ($hits >= 0 ? '&a(+' . $hits . ')' : '&c(-' . $hits . ')'),
                '  &aYou: &f' . $playerHits . ($playerCombo > 0 ? ' &e(' . $playerCombo . ' combo)' : ''),
                '  &cThem: &f' . $opponentHits . ($opponentCombo > 0 ? ' &e(' . $opponentCombo . ' combo)' : ''),
                ' &r&r&r&r',
                ' &aYour ping: ' . $player->getNetworkSession()->getPing(),
                ' &cTheir ping: ' . $opponent->getNetworkSession()->getPing()
            ];
        }
        return parent::scoreboard($player);
    }
    
    /*public function update(): void {
        parent::update();
        
        if ($this->status === self::RUNNING) {
            $firstSession = $this->firstSession;
            $secondSession = $this->secondSession;
        
            if ($this->firstHit >= 100) {
                $this->finish($secondSession->getPlayer());
            } elseif ($this->secondHit >= 100) {
                $this->finish($firstSession->getPlayer());
            }
        }
    }*/
}