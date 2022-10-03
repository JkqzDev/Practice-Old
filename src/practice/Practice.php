<?php

declare(strict_types=1);

namespace practice;

use practice\duel\DuelManager;
use practice\session\SessionFactory;
use pocketmine\plugin\PluginBase;

class Practice extends PluginBase {

    static private Practice $instance;
    
    protected function onLoad(): void {
        self::$instance = $this;
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

    static public function getInstance(): Practice {
        return self::$instance;
    }
    
    public function getDuelManager(): DuelManager {
        return $this->duelManager;
    }
}