<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\color\Color;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\duel\DuelFactory;
use practice\duel\Duel;
use practice\kit\KitFactory;
use practice\world\WorldFactory;

class BattleRush extends Duel {
    
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
            floatval($firstPortal->getX() + 1),
            floatval($firstPortal->getY()),
            floatval($firstPortal->getZ() + 1)
        );
        
        $this->secondPortal = new AxisAlignedBB(
            floatval($secondPortal->getX()),
            floatval($secondPortal->getY()),
            floatval($secondPortal->getZ()),
            floatval($secondPortal->getX() + 1),
            floatval($secondPortal->getY()),
            floatval($secondPortal->getZ() + 1)
        );
    }
    
    private function addPoint(bool $firstPlayer = true): void {
        if ($firstPlayer) {
            $this->firstPoints++;
        } else {
            $this->secondPoints++;
        }
    }
    
    private function giveKit(Player $player, bool $firstPlayer = true): void {
        $kit = KitFactory::get(strtolower(DuelFactory::getName($this->typeId)));
        
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        
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
                    $inventoryContents[$slot] = ItemFactory::getInstance()->get($item->getId(), $firstPlayer ? 11 : 13, $item->getCount());
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
            $player->teleport(Position::fromObject($firstPosition, $this->world));
        } else {
            $player->teleport(Position::fromObject($secondPosition, $this->world));
        }
    }
    
    public function prepare(): void {
        $worldName = $this->worldName;
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
        }
    }
    
    public function update(): void {
        parent::update();
        
        if ($this->status === self::RUNNING) {
            $firstSession = $this->firstSession;
            $secondSession = $this->secondSession;
            
            $firstPlayer = $firstSession->getPlayer();
            $secondPlayer = $secondSession->getPlayer();
            
            if ($firstPlayer !== null && $secondPlayer !== null) {
                if ($firstPlayer->getPosition()->getY() < 0) {
                    $this->teleportPlayer($firstPlayer);
                } elseif ($secondPlayer->getPosition()->getY() < 0) {
                    $this->teleportPlayer($secondPlayer, false);
                }
            }
        }
    }
}