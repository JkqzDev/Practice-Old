<?php

declare(strict_types=1);

namespace practice;

use pocketmine\block\tile\Sign;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use practice\item\duel\DuelLeaveItem;
use practice\kit\KitFactory;
use practice\session\SessionFactory;
use practice\session\setting\display\CPSCounter;
use practice\session\setting\Setting;

final class EventHandler implements Listener {

    public function handleBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inLobby()) {
            if ($player->getServer()->isOp($player->getName())) {
                return;
            }
            $event->cancel();
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handleBreak($event);
        } elseif ($session->inArena()) {
            $arena = $session->getArena();
            $arena->handleBreak($event);
        } elseif ($session->inParty()) {
            $party = $session->getParty();

            if ($party->inDuel()) {
                $duel = $party->getDuel();
                $duel->handleBreak($event);
            }
        }
    }

    public function handlePlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inLobby()) {
            if ($player->getServer()->isOp($player->getName())) {
                return;
            }
            $event->cancel();
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handlePlace($event);
        } elseif ($session->inArena()) {
            $arena = $session->getArena();
            $arena->handlePlace($event);
        } elseif ($session->inParty()) {
            $party = $session->getParty();

            if ($party->inDuel()) {
                $duel = $party->getDuel();
                $duel->handlePlace($event);
            }
        }
    }

    public function handleSpread(BlockSpreadEvent $event): void {
        $event->uncancel();
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
                /** @var World $world */
                $world = $player->getServer()->getWorldManager()->getDefaultWorld();
                $player->teleport($world->getSpawnLocation());
            }
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handleDamage($event);
        } elseif ($session->inArena()) {
            $arena = $session->getArena();
            $arena->handleDamage($event);
        } elseif ($session->inParty()) {
            $party = $session->getParty();

            if ($party->inDuel()) {
                $duel = $party->getDuel();
                $duel->handleDamage($event);
            }
        }


        if (!$event->isCancelled() && $event instanceof EntityDamageByEntityEvent) {
            if ($event->getModifier(EntityDamageEvent::MODIFIER_PREVIOUS_DAMAGE_COOLDOWN) < 0.0) {
                $event->cancel();
                return;
            }
            $damager = $event->getDamager();
            $kit = KitFactory::get($session->getCurrentKit());

            if ($damager instanceof Player && $kit !== null) {
                $event->setKnockBack(0.0);
                $event->setAttackCooldown($kit->getAttackCooldown());

                $session->knockback($damager, $kit);
            }
        }
    }

    public function handleMotion(EntityMotionEvent $event): void {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->initialKnockbackMotion) {
            $session->initialKnockbackMotion = false;
            $session->cancelKnockbackMotion = true;
        } elseif ($session->cancelKnockbackMotion) {
            $session->cancelKnockbackMotion = false;
            $event->cancel();
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

    public function handleCraft(CraftItemEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->getCurrentKitEdit() !== null) {
            $event->cancel();
            return;
        }
    }

    public function handleTransaction(InventoryTransactionEvent $event): void {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();

        $session = SessionFactory::get($player);

        if ($session === null) {
            $event->cancel();
            return;
        }

        if ($session->getCurrentKitEdit() !== null) {
            $inventories = $transaction->getInventories();

            foreach ($inventories as $inventory) {
                if ($inventory instanceof PlayerOffHandInventory) {
                    $event->cancel();
                    return;
                }
            }
            return;
        }

        foreach ($transaction->getActions() as $action) {
            $item = $action->getSourceItem();

            if ($item->getNamedTag()->getTag('practice_item') !== null) {
                $event->cancel();
            }
        }
    }

    public function handleDropItem(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inArena()) {
            $event->cancel();
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();

            if (!$duel->canDrop()) {
                $event->cancel();
            }
        }
    }

    public function handleExhaust(PlayerExhaustEvent $event): void {
        $event->cancel();
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $action = $event->getAction();
        $item = $event->getItem();
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inLobby()) {
            $handlerSetupArena = $session->getSetupArenaHandler();
            $handlerSetupDuel = $session->getSetupDuelHandler();
            $currentKitEdit = $session->getCurrentKitEdit();

            if ($handlerSetupArena !== null) {
                $handlerSetupArena->handleInteract($event);
            } elseif ($handlerSetupDuel !== null) {
                $handlerSetupDuel->handleInteract($event);
            } elseif ($currentKitEdit !== null) {
                $block = $event->getBlock();
                $tile = $player->getWorld()->getTile($block->getPosition());

                if (!$tile instanceof Sign) {
                    return;
                }

                if ($action !== PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                    return;
                }
                $text = $tile->getText();

                switch ($text->getLine(0)) {
                    case TextFormat::colorize('&aSave'):
                        $currentKitEdit->setInventoryContents($player->getInventory()->getContents());
                        $player->sendMessage(TextFormat::colorize('&aKit edited successfully.'));
                        break;

                    case TextFormat::colorize('&fReset'):
                        $player->getInventory()->setContents($currentKitEdit->getRealKit()->getInventoryContents());
                        $player->sendMessage(TextFormat::colorize('&aYou have reset the kit.'));
                        break;

                    case TextFormat::colorize('&cExit'):
                        $session->setCurrentKitEdit(null);
                        $session->giveLobbyItems();

                        $player->setImmobile(false);
                        $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());

                        foreach ($player->getServer()->getOnlinePlayers() as $target) {
                            if (!$target->canSee($player)) {
                                $target->showPlayer($player);
                            }
                        }
                        break;
                }
            }
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();

            if ($duel->isSpectator($player) && $item instanceof DuelLeaveItem) {
                $session->setDuel(null);
                $session->giveLobbyItems();

                $player->setGamemode(GameMode::SURVIVAL());
                $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            }
        }
    }

    public function handleItemUse(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inLobby()) {
            if ($session->getCurrentKitEdit() !== null) {
                $event->cancel();
            }
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handleItemUse($event);
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

        if (!$player->getServer()->isWhitelisted($player->getName())) {
            $event->setKickMessage(TextFormat::colorize(Practice::getInstance()->getConfig()->get('server-whitelist', '')));
            $event->cancel();
            return;
        }
        $session = SessionFactory::get($player);

        if ($session === null) {
            SessionFactory::create($player);
        } else {
            if ($session->getName() !== $player->getName()) {
                $session->setName($player->getName());
            }
        }
    }

    public function handleMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inDuel()) {
            $duel = $session->getDuel();
            $duel->handleMove($event);
        } elseif ($session->inParty()) {
            $party = $session->getParty();

            if ($party->inDuel()) {
                $duel = $party->getDuel();
                $duel->handleMove($event);
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

    public function handlePacketReceive(DataPacketReceiveEvent $event): void {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if ($player === null) {
            return;
        }
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($packet instanceof AnimatePacket) {
            if ($packet->action === AnimatePacket::ACTION_SWING_ARM) {
                $event->cancel();
                $player->getServer()->broadcastPackets($player->getViewers(), [$packet]);
            }
            return;
        }

        $cpsSetting = $session->getSetting(Setting::CPS_COUNTER);

        if ($cpsSetting instanceof CPSCounter && $cpsSetting->isEnabled()) {

            if ($packet instanceof LevelSoundEventPacket) {
                if ($packet->sound === LevelSoundEvent::ATTACK_STRONG || $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                    $cpsSetting->addClick();

                    $player->sendTip(TextFormat::colorize('&cCPS: &f' . $cpsSetting->getCPS()));
                }
            }
        }
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