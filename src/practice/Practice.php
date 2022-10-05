<?php

declare(strict_types=1);

namespace practice;

use practice\duel\DuelManager;
use practice\session\SessionFactory;
use pocketmine\plugin\PluginBase;
use practice\arena\ArenaManager;
use practice\kit\KitFactory;
use practice\world\WorldFactory;

class Practice extends PluginBase {

    public const IS_DEVELOPING = true;

    static private Practice $instance;

    private ArenaManager $arenaManager;
    private DuelManager $duelManager;
    
    protected function onLoad(): void {
        self::$instance = $this;
    }
    
    protected function onEnable(): void {
        KitFactory::loadAll();
        SessionFactory::loadAll();
        WorldFactory::loadAll();

        SessionFactory::task();
        
        $this->arenaManager = new ArenaManager;
        $this->duelManager = new DuelManager;
        
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
    }
    
    protected function onDisable(): void {
        KitFactory::saveAll();
        SessionFactory::saveAll();
        WorldFactory::saveAll();
    }

    static public function getInstance(): Practice {
        return self::$instance;
    }

    public function getArenaManager(): ArenaManager {
        return $this->arenaManager;
    }
    
    public function getDuelManager(): DuelManager {
        return $this->duelManager;
    }
}