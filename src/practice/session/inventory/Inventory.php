<?php

declare(strict_types=1);

namespace practice\session\inventory;

use pocketmine\item\Item;
use practice\kit\Kit;
use practice\session\Session;

final class Inventory {

    /**
     * @param Item[] $inventoryContents
     */
    public function __construct(
        private Session $session,
        private Kit   $kit,
        private array $inventoryContents,
        private bool  $update = false
    ) {}

    static public function deserializeData(Session $session, array $data, Kit $kit): self {
        $newInventory = [];

        foreach ($data as $slot => $itemSerialize) {
            $newInventory[(int) $slot] = Item::jsonDeserialize($itemSerialize);
        }
        return new self($session, $kit, $newInventory);
    }

    public function getRealKit(): Kit {
        return $this->kit;
    }

    public function getInventoryContents(): array {
        return $this->inventoryContents;
    }

    public function isUpdate(): bool {
        return $this->update;
    }

    public function setInventoryContents(array $inventoryContents): void {
        $this->inventoryContents = $inventoryContents;
        $this->update = true;
    }

    public function giveKit(): void {
        $player = $this->session->getPlayer();

        if ($player === null) {
            return;
        }
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

    public function serializeData(): array {
        $data = [];

        foreach ($this->inventoryContents as $slot => $item) {
            $data[(string) $slot] = $item->jsonSerialize();
        }
        return $data;
    }
}