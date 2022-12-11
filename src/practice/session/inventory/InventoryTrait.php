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
                    MySQL::runAsync(new InsertAsync('player_inventories', ['xuid' => $this->xuid, 'no_debuff' => '', 'battle_rush' => '', 'boxing' => '', 'bridge' => '', 'build_uhc' => '', 'cave_uhc' => '', 'combo' => '', 'final_uhc' => '', 'fist' => '', 'gapple' => '', 'sumo' => '']));
                } else {
                    $row = $rows[0];
                    $inventoriesName = ['no_debuff', 'battle_rush', 'boxing', 'bridge', 'build_uhc', 'cave_uhc', 'combo', 'final_uhc', 'fist', 'gapple', 'sumo'];

                    foreach ($row as $name => $data) {
                        if (!isset($inventoriesName[$name]))  {
                            continue;
                        }
                        $realName = str_replace('_', ' ', $name);

                        if (KitFactory::get($realName) === null) {
                            continue;
                        }
                        $this->inventories[$realName] = Inventory::deserializeData(json_decode(base64_decode($data)), KitFactory::get($realName));
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
        MySQL::runAsync(new UpdateAsync('player_inventories', [
            'no_debuff' => base64_encode(json_encode($this->getInventory('no debuff')->serializeData())),
            'battle_rush' => base64_encode(json_encode($this->getInventory('battle rush')->serializeData())),
            'boxing' => base64_encode(json_encode($this->getInventory('boxing')->serializeData())),
            'bridge' => base64_encode(json_encode($this->getInventory('bridge')->serializeData())),
            'build_uhc' => base64_encode(json_encode($this->getInventory('build uhc')->serializeData())),
            'cave_uhc' => base64_encode(json_encode($this->getInventory('cave uhc')->serializeData())),
            'combo' => base64_encode(json_encode($this->getInventory('combo')->serializeData())),
            'final_uhc' => base64_encode(json_encode($this->getInventory('final uhc')->serializeData())),
            'fist' => base64_encode(json_encode($this->getInventory('fist')->serializeData())),
            'gapple' => base64_encode(json_encode($this->getInventory('gapple')->serializeData())),
            'sumo' => base64_encode(json_encode($this->getInventory('sumo')->serializeData()))
        ], ['xuid' => $this->xuid]));
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