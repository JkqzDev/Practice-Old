<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\duel\DuelFactory;
use practice\duel\Duel;
use practice\kit\KitFactory;
use practice\world\WorldFactory;

class Bridge extends Duel {
    
    private const STARTING_BATTLE = 0;
    private const RUNNING_BATTLE = 1;

    private int $mode = self::RUNNING_BATTLE;

    private int $firstPoints = 0, $secondPoints = 0;
    private AxisAlignedBB $firstPortal, $secondPortal;
    
    protected function init(): void {
        $worldName = $this->worldName;
        $worldData = WorldFactory::get($worldName);
        
        $firstPortal = $worldData->getFirstPortal();
        $secondPortal = $worldData->getSecondPortal();
        
        $this->firstPortal = new AxisAlignedBB(
            floatval($firstPortal->getX()),
            floatval($firstPortal->getY()),
            floatval($firstPortal->getZ()),
            floatval($firstPortal->getX()),
            floatval($firstPortal->getY()),
            floatval($firstPortal->getZ())
        );
        $this->firstPortal->expand(8.0, 30.0, 8.0);
        
        $this->secondPortal = new AxisAlignedBB(
            floatval($secondPortal->getX()),
            floatval($secondPortal->getY()),
            floatval($secondPortal->getZ()),
            floatval($secondPortal->getX()),
            floatval($secondPortal->getY()),
            floatval($secondPortal->getZ())
        );
        $this->secondPortal->expand(8.0, 30.0, 8.0);
    }
    
    private function addPoint(bool $firstPlayer = true): void {
        if ($firstPlayer) {
            $this->firstPoints++;
        } else {
            $this->secondPoints++;
        }
        $firstPlayer = $this->firstSession->getPlayer();
        $secondPlayer = $this->secondSession->getPlayer();

        $this->starting = 5;
        $this->mode = self::STARTING_BATTLE;

        $this->teleportPlayer($firstPlayer);
        $this->teleportPlayer($secondPlayer, false);

        if ($this->firstPoints >= 5) {
            $this->finish($secondPlayer);
            return;
        }

        if ($this->secondPoints >= 5) {
            $this->finish($firstPlayer);
            return;
        }
        $this->giveKit($firstPlayer);
        $this->giveKit($secondPlayer, false);

        $firstPlayer->setImmobile(true);
        $secondPlayer->setImmobile(true);

        $title = ($firstPlayer ? '&9' . $firstPlayer->getName() : '&c' . $secondPlayer->getName()) . ' &escored!';
        $subTitle = '&9' . $this->firstPoints . ' &7- &c' . $this->secondPoints;

        $firstPlayer->sendTitle(TextFormat::colorize($title), TextFormat::colorize($subTitle));
        $secondPlayer->sendTitle(TextFormat::colorize($title), TextFormat::colorize($subTitle));
    }
    
    private function giveKit(Player $player, bool $firstPlayer = true): void {
        $kit = KitFactory::get(strtolower(DuelFactory::getName($this->typeId)));
        
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        
        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        if ($kit !== null) {
            $armorContents = $kit->getArmorContents();
            $inventoryContents = $kit->getInventoryContents();
            $effects = $kit->getEffects();
            
            $color = new Color(0, 0, 255);
            
            if (!$firstPlayer) {
                $color = new Color(255, 0, 0);
            }
            
            foreach ($armorContents as $slot => $item) {
                $armorContents[$slot] = $item->setCustomColor($color);
            }
            
            foreach ($inventoryContents as $slot => $item) {
                if ($item->getId() === ItemIds::WOOL) {
                    $inventoryContents[$slot] = ItemFactory::getInstance()->get($item->getId(), $firstPlayer ? 11 : 14, $item->getCount());
                }
            }
            $player->getArmorInventory()->setContents($armorContents);
            $player->getInventory()->setContents($inventoryContents);
            $effectManager = $player->getEffects();
        
            foreach ($effects as $effect) {
                $effectManager->add($effect);
            }
        }
    }
    
    private function teleportPlayer(Player $player, bool $firstPlayer = true): void {
        $worldName = $this->worldName;
        $worldData = WorldFactory::get($worldName);
        $firstPosition = $worldData->getFirstPosition();
        $secondPosition = $worldData->getSecondPosition();
        
        if ($firstPlayer) {
            $player->teleport(Position::fromObject($firstPosition->add(0.5, 0, 0.5), $this->world));
        } else {
            $player->teleport(Position::fromObject($secondPosition->add(0.5, 0, 0.5), $this->world));
        }
    }

    public function handleBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        
        if (!isset($this->blocks[$block->getPosition()->__toString()])) {
            $event->cancel();
            return;
        }
        
        unset($this->blocks[$block->getPosition()->__toString()]);
    }
    
    public function handlePlace(BlockPlaceEvent $event): void {
        $block = $event->getBlock();
        
        $firstPortal = $this->firstPortal;
        $secondPortal = $this->secondPortal;

        if ($firstPortal->isVectorInside($block->getPosition()) || $secondPortal->isVectorInside($block->getPosition())) {
            $event->cancel();
            return;
        }
        $this->blocks[$block->getPosition()->__toString()] = $block;
    }

    public function handleDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        
        if (!$player instanceof Player) {
            return;
        }
        $finalHealth = $player->getHealth() - $event->getFinalDamage();
        
        if (!$this->isRunning() || $this->mode === self::STARTING_BATTLE) {
            $event->cancel();
            return;
        }
            
        if ($finalHealth <= 0.00) {
            $event->cancel();
            $isFirst = $player->getName() === $this->firstSession->getName();

            $this->giveKit($player, $isFirst);
            $this->teleportPlayer($player, $isFirst);
        }
    }

    public function handleMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $isFirst = $player->getName() === $this->firstSession->getName();

        $ownPortal = $isFirst ? $this->firstPortal : $this->secondPortal;
        $opponentPortal = $isFirst ? $this->secondPortal : $this->firstPortal;

        if ($ownPortal->isVectorInside($player->getPosition())) {
            $block = $player->getWorld()->getBlock($player->getPosition());
            
            if ($block->getId() === 119) {
                $this->teleportPlayer($player, $isFirst);
                $this->giveKit($player, $isFirst);
                return;
            }
        }

        if ($opponentPortal->isVectorInside($player->getPosition())) {
            $block = $player->getWorld()->getBlock($player->getPosition());
            
            if ($block->getId() === 119) {
                $this->addPoint($isFirst);
                return;
            }
        }
    }
    
    public function prepare(): void {
        $world = $this->world;
        
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        
        $world->setTime(World::TIME_MIDNIGHT);
        $world->stopTime();

        $firstPlayer = $firstSession->getPlayer();
        $secondPlayer = $secondSession->getPlayer();
        
        if ($firstPlayer !== null && $secondPlayer !== null) {
            $firstPlayer->setGamemode(GameMode::SURVIVAL());
            $secondPlayer->setGamemode(GameMode::SURVIVAL());
            
            $firstPlayer->getArmorInventory()->clearAll();
            $firstPlayer->getInventory()->clearAll();
            $secondPlayer->getArmorInventory()->clearAll();
            $secondPlayer->getInventory()->clearAll();
            
            $this->giveKit($firstPlayer);
            $this->giveKit($secondPlayer, false);
            
            $this->teleportPlayer($firstPlayer);
            $this->teleportPlayer($secondPlayer, false);
            
            $firstPlayer->setImmobile(true);
            $secondPlayer->setImmobile(true);
        }
    }

    public function scoreboard(Player $player): array {
        if ($this->status === self::RUNNING) {
            $firstPoints = $this->firstPoints;
            $secondPoints = $this->secondPoints;

            if ($this->isSpectator($player)) {
                return [
                    ' &9[B] &9' . str_repeat('█', $firstPoints) . ' &7' . str_repeat('█', 5 - $firstPoints),
                    ' &c[R] &c' . str_repeat('█', $secondPoints) . ' &7' . str_repeat('█', 5 - $secondPoints),
                    ' &r ',
                    ' &fDuration: &b' . gmdate('i:s', $this->running)
                ];
            }
            $opponent = $this->getOpponent($player);

            return [
                ' &9[B] &9' . str_repeat('█', $firstPoints) . ' &7' . str_repeat('█', 5 - $firstPoints),
                ' &c[R] &c' . str_repeat('█', $secondPoints) . ' &7' . str_repeat('█', 5 - $secondPoints),
                ' &r ',
                ' &fDuration: &b' . gmdate('i:s', $this->running),
                ' &r&r ',
                ' &aYour ping: ' . $player->getNetworkSession()->getPing(),
                ' &cTheir ping: ' . $opponent->getNetworkSession()->getPing()
            ];
        }
        return parent::scoreboard($player);
    }
    
    public function update(): void {
        parent::update();
        
        if ($this->status === self::RUNNING) {
            $firstPlayer = $this->firstSession->getPlayer();
            $secondPlayer = $this->secondSession->getPlayer();

            if ($this->mode === self::STARTING_BATTLE) {
                if ($this->starting <= 0) {
                    $this->mode = self::RUNNING_BATTLE;

                    if ($firstPlayer->isImmobile()) {
                        $firstPlayer->setImmobile(false);
                    }

                    if ($secondPlayer->isImmobile()) {
                        $secondPlayer->setImmobile(false);
                    }
                    return;
                }
                $this->starting--;
                return;
            }

            if ($firstPlayer->getPosition()->getY() < 0) {
                $this->teleportPlayer($firstPlayer);
                $this->giveKit($firstPlayer);
            } elseif ($secondPlayer->getPosition()->getY() < 0) {
                $this->teleportPlayer($secondPlayer, false);
                $this->giveKit($secondPlayer, false);
            }
        }
    }
}