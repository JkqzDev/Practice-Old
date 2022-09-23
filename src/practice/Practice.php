<?php

declare(strict_types=1);

namespace practice;

use practice\duel\DuelManager;
use practice\session\SessionFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Practice extends PluginBase {
    use SingletonTrait;
    
    protected function onLoad(): void {
        self::setInstance($this);
    }
    
    protected function onEnable(): void {
        SessionFactory::loadAll();
        SessionFactory::task();
        
        $this->duelManager = new DuelManager;
        
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
    }
    
    protected function onDisable(): void {
        SessionFactory::saveAll();
    }
    
    public function getDuelManager(): DuelManager {
        return $this->duelManager;
    }
}