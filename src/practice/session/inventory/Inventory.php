<?php

declare(strict_types=1);

namespace practice\session\inventory;

use pocketmine\item\Item;
use pocketmine\player\Player;
use practice\kit\Kit;

final class Inventory {

    /**
     * @param Item[] $inventoryContents
     */
    public function __construct(
        private Kit $kit,
        private array $inventoryContents
    ) {}

    public function getRealKit(): Kit {
        return $this->kit;
    }

    public function getInventoryContents(): array {
        return $this->inventoryContents;
    }

    public function setInventoryContents(array $inventoryContents): void {
        $this->inventoryContents = $inventoryContents;
    }

    public function giveTo(Player $player): void {
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->getArmorInventory()->setContents($this->kit->getArmorContents());
        $player->getInventory()->setContents($this->inventoryContents);
        $player->getInventory()->setHeldItemIndex(0);
        $effectManager = $player->getEffects();

        foreach ($this->kit->getEffects() as $effect) {
            $effectManager->add($effect);
        }
    }
}