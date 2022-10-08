<?php

declare(strict_types=1);

namespace practice;

use pocketmine\plugin\PluginBase;
use practice\arena\ArenaFactory;
use practice\duel\DuelFactory;
use practice\kit\KitFactory;
use practice\session\SessionFactory;
use practice\world\WorldFactory;

class Practice extends PluginBase {

    public const IS_DEVELOPING = true;

    static private Practice $instance;
    
    protected function onLoad(): void {
        self::$instance = $this;
    }
    
    protected function onEnable(): void {
        ArenaFactory::loadAll();
        KitFactory::loadAll();
        SessionFactory::loadAll();
        WorldFactory::loadAll();
        
        DuelFactory::task();
        SessionFactory::task();
        
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
    }
    
    protected function onDisable(): void {
        ArenaFactory::saveAll();
        KitFactory::saveAll();
        SessionFactory::saveAll();
        WorldFactory::saveAll();
    }

    static public function getInstance(): Practice {
        return self::$instance;
    }
}