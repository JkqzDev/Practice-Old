<?php

declare(strict_types=1);

namespace practice\session\inventory;

use practice\database\mysql\MySQL;
use practice\database\mysql\queries\SelectAsync;
use practice\kit\KitFactory;

trait InventoryTrait {

    /** @var Inventory[] */
    private array $inventories = [];
    private ?Inventory $currentKitEdit = null;

    private function initInventories(): void {
        MySQL::runAsync(new SelectAsync('player_inventories', ['xuid' => $this->xuid], '',
            function (array $rows): void {
                if (count($rows) === 0) {
                    foreach (KitFactory::getAll() as $name => $kit) {
                        $this->inventories[$name] = new Inventory($kit, $kit->getInventoryContents());
                    }
                }
            })
        );
    }

    public function getInventory(string $name): ?Inventory {
        return $this->inventories[$name] ?? null;
    }

    public function getCurrentKitEdit(): ?Inventory {
        return $this->currentKitEdit;
    }

    public function setCurrentKitEdit(?Inventory $currentKitEdit): void {
        $this->currentKitEdit = $currentKitEdit;
    }
}