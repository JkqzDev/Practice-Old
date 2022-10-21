<?php

declare(strict_types=1);

namespace practice;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use practice\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class EventHandler implements Listener {
    
    public function handleBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return;
        }
        
        if ($session->inLobby()) {
            $event->cancel();
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handleBreak($event);
        } elseif ($session->inArena()) {
            $arena = $session->getArena();
            $arena->handleBreak($event);
        }
    }
    
    public function handlePlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return;
        }
        
        if ($session->inLobby()) {
            $event->cancel();
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handlePlace($event);
        } elseif ($session->inArena()) {
            $arena = $session->getArena();
            $arena->handlePlace($event);
        }
    }
    
    public function handleDecay(LeavesDecayEvent $event): void {
        $event->cancel();
    }

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
        
        if ($cause === EntityDamageEvent::CAUSE_FALL) {
            $event->cancel();
            return;
       }

        if ($session->inLobby()) {
            $event->cancel();

            if ($cause === EntityDamageEvent::CAUSE_VOID) {
                $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            }
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handleDamage($event);
        } elseif ($session->inArena()) {
            $arena = $session->getArena();
            $arena->handleDamage($event);
        }
    }
    
    public function handleRegainHealth(EntityRegainHealthEvent $event): void {
        $cause = $event->getRegainReason();
        $entity = $event->getEntity();
        
        if (!$entity instanceof Player) {
            return;
        }
        
        if ($cause === EntityRegainHealthEvent::CAUSE_SATURATION) {
            $event->cancel();
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
    
    public function handleExhaust(PlayerExhaustEvent $event): void {
        $event->cancel();
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inLobby()) {
            $handlerSetupArena = $session->getSetupArenaHandler();
            $handlerSetupDuel = $session->getSetupDuelHandler();

            if ($handlerSetupArena !== null) {
                $handlerSetupArena->handleInteract($event);
            } elseif ($handlerSetupDuel !== null) {
                $handlerSetupDuel->handleInteract($event);
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
        } else {
            if ($session->getName() !== $player->getName()) {
                $session->setName($player->getName());
            }
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
    
    public function handlePacketSend(DataPacketSendEvent $event): void {
        $packets = $event->getPackets();
        
        foreach ($packets as $packet) {
            if ($packet instanceof LevelSoundEventPacket) {
                if ($packet->sound === LevelSoundEvent::ATTACK_STRONG || $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                    $event->cancel();
                }
            }
        }
    }
}