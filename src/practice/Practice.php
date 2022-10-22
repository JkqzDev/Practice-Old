<?php

declare(strict_types=1);

namespace practice;

use pocketmine\plugin\PluginBase;
use practice\arena\ArenaFactory;
use practice\arena\command\ArenaCommand;
use practice\duel\DuelFactory;
use practice\kit\KitFactory;
use practice\session\SessionFactory;
use practice\world\WorldFactory;

final class Practice extends PluginBase {

    public const IS_DEVELOPING = false;

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
        
        $this->registerHandlers();
        $this->registerCommands();
        $this->unregisterCommands();
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
    
    protected function registerHandlers(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
    }
    
    protected function registerCommands(): void {
        $commands = [
            // Arena
            new ArenaCommand
        ];

        foreach ($commands as $command) {
            $this->getServer()->getCommandMap()->register('Practice', $command);
        }
    }
    
    protected function unregisterCommands(): void {
        $commands = [
            'me',
            'kill',
            'suicide',
            'clear',
        ];
        
        foreach ($commands as $commandName) {
            $command = $this->getServer()->getCommandMap()->getCommand($commandName);
            
            if ($command !== null) {
                $this->getServer()->getCommandMap()->unregister($command);
            }
        }
    }
}