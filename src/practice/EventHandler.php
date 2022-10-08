<?php

declare(strict_types=1);

namespace practice;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use practice\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EventHandler implements Listener {

    public function handleDamage(EntityDamageEvent $event): void {
        $cause = $event->getCause();
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inLobby()) {
            $event->cancel();

            if ($cause === EntityDamageEvent::CAUSE_VOID) {
                $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            }
        }
    }

    public function handleTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();

        foreach ($transaction->getActions() as $action) {
            $item = $action->getSourceItem();
            
            if ($item->getNamedTag()->getTag('practice_item') !== null) {
                $event->cancel();
            }
        }
    }
    
    public function handleJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return;
        }
        $session->join();
        
        $event->setJoinMessage(TextFormat::colorize('&7[&a+&7] &a' . $player->getName()));
    }
    
    public function handleLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            SessionFactory::create($player);
        }
    }

    public function handleQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }
        $session->quit();

        $event->setQuitMessage(TextFormat::colorize('&7[&c-&7] &c' . $player->getName()));
    }
}