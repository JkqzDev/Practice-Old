<?php

declare(strict_types=1);

namespace practice\duel\type;

use practice\duel\Duel;
use pocketmine\player\Player;
use practice\duel\DuelFactory;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Boxing extends Duel {

    private int $firstHit = 0, $secondHit = 0;
    private int $firstCombo = 0, $secondCombo = 0;

    public function handleDamage(EntityDamageEvent $event): void {
        parent::handleDamage($event);
        $player = $event->getEntity();

        if ($event->isCancelled()) {
            return;
        }
        $player->setHealth($player->getMaxHealth());

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

            if ($this->isSpectator($player)) {
                return [
                    ' &fKit: &c' . DuelFactory::getName($this->typeId),
                    ' &fType: &c' . ($this->ranked ? 'Ranked' : 'Unranked'),
                    ' &r&r',
                    ' &fDuration: &c' . gmdate('i:s', $this->running),
                    ' &fSpectators: &c' . count($this->spectators),
                    ' &fHits: &a' . $this->firstHit . ' &7| &c' . $this->secondHit
                ];
            }
            /** @var Player $opponent */
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
                ' &fFighting: &c' . $opponent->getName(),
                ' &r&r&r',
                ' &cHits: ' . ($hits >= 0 ? '&a(+' . $hits . ')' : '&c(-' . $hits . ')'),
                '  &aYou: &f' . $playerHits . ($playerCombo > 0 ? ' &e(' . $playerCombo . ' combo)' : ''),
                '  &cThem: &f' . $opponentHits . ($opponentCombo > 0 ? ' &e(' . $opponentCombo . ' combo)' : ''),
                ' &r&r&r&r',
                ' &aYour ping: ' . $player->getNetworkSession()->getPing(),
                ' &cTheir ping: ' . $opponent->getNetworkSession()->getPing()
            ];
        }
        return parent::scoreboard($player);
    }
}