<?php

declare(strict_types=1);

namespace practice\session\inventory;

use practice\database\mysql\MySQL;
use practice\database\mysql\queries\InsertAsync;
use practice\database\mysql\queries\SelectAsync;
use practice\database\mysql\queries\UpdateAsync;
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
                            $this->inventories[$name] = new Inventory($kit, $kit->getInventoryContents(), true);
                        }
                        $serialize = base64_encode(json_encode([]));
                        MySQL::runAsync(new InsertAsync('player_inventories', ['xuid' => $this->xuid, 'player' => $this->name, 'no_debuff' => $serialize, 'battle_rush' => $serialize, 'boxing' => $serialize, 'bridge' => $serialize, 'build_uhc' => $serialize, 'cave_uhc' => $serialize, 'combo' => $serialize, 'final_uhc' => $serialize, 'fist' => $serialize, 'gapple' => $serialize, 'sumo' => $serialize]));
                    } else {
                        $row = $rows[0];

                        foreach ($row as $name => $data) {
                            $realName = str_replace('_', ' ', $name);

                            if (KitFactory::get($realName) === null) {
                                continue;
                            }
                            $this->inventories[$realName] = Inventory::deserializeData(json_decode(base64_decode($data), true), KitFactory::get($realName));
                        }
                    }
                })
        );
    }

    private function updateInventories(): void {
        $inventories = array_filter($this->inventories, function (Inventory $inventory): bool {
            return $inventory->isUpdate();
        });

        if (count($inventories) === 0) {
            return;
        }
        $data = [
            'player' => $this->name
        ];

        foreach ($this->inventories as $name => $inventory) {
            $data[strtolower(str_replace(' ', '_', $name))] = base64_encode(json_encode($inventory->serializeData()));
        }
        MySQL::runAsync(new UpdateAsync('player_inventories', $data, ['xuid' => $this->xuid]));
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