<?php

declare(strict_types=1);

namespace practice\kit;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;

final class Kit {

    public function __construct(
        private int $attackCooldown,
        private float $maxHeight,
        private float $horizontalKnockback,
        private float $verticalKnockback,
        private bool $canRevert,
        private array $armorContents,
        private array $inventoryContents,
        private array $effects
    ) {}
    
    public function getAttackCooldown(): int {
        return $this->attackCooldown;
    }
    
    public function getMaxHeight(): float {
        return $this->maxHeight;
    }
    
    public function getHorizontalKnockback(): float {
        return $this->horizontalKnockback;
    }
    
    public function getVerticalKnockback(): float {
        return $this->verticalKnockback;
    }
    
    public function canRevert(): bool {
        return $this->canRevert;
    }
    
    public function getArmorContents(): array {
        return $this->armorContents;
    }
    
    public function getInventoryContents(): array {
        return $this->inventoryContents;
    }
    
    public function getEffects(): array {
        return $this->effects;
    }
    
    public function setAttackCooldown(int $attackCooldown): void {
        $this->attackCooldown = $attackCooldown;
    }
    
    public function setMaxHeight(float $maxHeight): void {
        $this->maxHeight = $maxHeight;
    }
    
    public function setHorizontalKnockback(float $horizontalKnockback): void {
        $this->horizontalKnockback = $horizontalKnockback;
    }
    
    public function setVerticalKnockback(float $verticalKnockback): void {
        $this->verticalKnockback = $verticalKnockback;
    }
    
    public function setCanRevert(bool $revert): void {
        $this->canRevert = $revert;
    }
    
    public function giveTo(Player $player): void {
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        
        $player->getArmorInventory()->setContents($this->armorContents);
        $player->getInventory()->setContents($this->inventoryContents);
        $player->getInventory()->setHeldItemIndex(0);
        $effectManager = $player->getEffects();
        
        foreach ($this->effects as $effect) {
            $effectManager->add($effect);
        }
    }
    
    public function serializeData(): array {
        return [
            'attackCooldown' => $this->attackCooldown,
            'maxHeight' => $this->maxHeight,
            'horizontalKnockback' => $this->horizontalKnockback,
            'verticalKnockback' => $this->verticalKnockback,
            'canRevert' => $this->canRevert
        ];
    }
    
    static public function deserializeData(array $data): array {
        $storage = [
            'attackCooldown' => intval($data['attackCooldown'] ?? 10),
            'maxHeight' => floatval($data['maxHeight'] ?? 0.0),
            'horizontalKnockback' => floatval($data['horizontalKnockback'] ?? 0.4),
            'verticalKnockback' => floatval($data['verticalKnockback'] ?? 0.4),
            'canRevert' => boolval($data['canRevert'] ?? false),
            'armorContents' => [],
            'inventoryContents' => [],
            'effects' => []
        ];
        
        $armorContents = $data['armorContents'] ?? [];
        $inventoryContents = $data['inventoryContents'] ?? [];
        $effects = $data['effects'] ?? [];
        
        foreach ($armorContents as $slot => $armor) {
            $item = ItemFactory::getInstance()->get(intval($armor['id']), intval($armor['meta']));
            
            if (isset($armor['unbreakable']) && $item instanceof Durable) {
                $item->setUnbreakable(boolval($armor['unbreakable']));
            }
            
            if (isset($armor['enchantments'])) {
                foreach ($armor['enchantments'] as $enchantId => $enchantLevel) {
                    $enchant = EnchantmentIdMap::getInstance()->fromId(intval($enchantId));
                    
                    if ($enchant !== null) {
                        $item->addEnchantment(new EnchantmentInstance($enchant, intval($enchantLevel)));
                    }
                }
            }
            $storage['armorContents'][$slot] = $item;
        }
        
        foreach ($inventoryContents as $slot => $it) {
            $item = ItemFactory::getInstance()->get(intval($it['id']), intval($it['meta']), intval($it['count'] ?? 1));
            
            if (isset($it['unbreakable']) && $item instanceof Durable) {
                $item->setUnbreakable(boolval($it['unbreakable']));
            }
            
            if (isset($it['enchantments'])) {
                foreach ($it['enchantments'] as $enchantId => $enchantLevel) {
                    $enchant = EnchantmentIdMap::getInstance()->fromId(intval($enchantId));
                    
                    if ($enchant !== null) {
                        $item->addEnchantment(new EnchantmentInstance($enchant, intval($enchantLevel)));
                    }
                }
            }
            $storage['inventoryContents'][$slot] = $item;
        }
        
        foreach ($effects as $id => $eff) {
            $effect = EffectIdMap::getInstance()->fromId(intval($id));
                    
            if ($effect !== null) {
                $storage['effects'][intval($id)] = new EffectInstance($effect, intval($eff['duration']), intval($eff['amplifier']), false);
            }
        }
        return $storage;
    }
}