<?php

declare(strict_types=1);

namespace practice;

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
    }
    
    protected function onDisable(): void {
        SessionFactory::saveAll();
    }
}