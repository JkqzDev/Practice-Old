<?php

declare(strict_types=1);

namespace practice;

use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use practice\arena\ArenaFactory;
use practice\arena\command\ArenaCommand;
use practice\duel\command\DuelCommand;
use practice\duel\DuelFactory;
use practice\entity\EnderPearl;
use practice\entity\SplashPotion;
use practice\item\EnderPearlItem;
use practice\item\GoldenHeadItem;
use practice\item\SplashPotionItem;
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
        
        $this->registerEntities();
        $this->registerItems();
        $this->registerHandlers();
        $this->registerCommands();
        $this->unregisterCommands();
    }
    
    protected function onDisable(): void {
        ArenaFactory::saveAll();
        KitFactory::saveAll();
        SessionFactory::saveAll();
        WorldFactory::saveAll();
        
        DuelFactory::disable();
    }

    static public function getInstance(): Practice {
        return self::$instance;
    }

    protected function registerEntities(): void {
        EntityFactory::getInstance()->register(EnderPearl::class, function (World $world, CompoundTag $nbt): EnderPearl {
			return new EnderPearl(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityLegacyIds::ENDER_PEARL);

		EntityFactory::getInstance()->register(SplashPotion::class, function (World $world, CompoundTag $nbt): SplashPotion {
			$potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort('PotionId', PotionTypeIds::WATER));

			if ($potionType === null) {
				throw new SavedDataLoadingException;
			}
			return new SplashPotion(EntityDataHelper::parseLocation($nbt, $world), null, $potionType, $nbt);

		}, ['ThrownPotion', 'minecraft:potion', 'thrownpotion'], EntityLegacyIds::SPLASH_POTION);
    }

    protected function registerItems(): void {
        ItemFactory::getInstance()->register(new EnderPearlItem, true);
        ItemFactory::getInstance()->register(new GoldenHeadItem, true);

        foreach (PotionType::getAll() as $potionType) {
            ItemFactory::getInstance()->register(new SplashPotionItem($potionType), true);
        }
    }
    
    protected function registerHandlers(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
    }
    
    protected function registerCommands(): void {
        $commands = [
            // Arena
            new ArenaCommand,
            // Duel
            new DuelCommand
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