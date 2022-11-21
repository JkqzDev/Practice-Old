<?php

declare(strict_types=1);

namespace practice;

use pocketmine\world\World;
use practice\kit\KitFactory;
use practice\duel\DuelFactory;
use pocketmine\item\PotionType;
use practice\entity\EnderPearl;
use pocketmine\item\ItemFactory;
use practice\arena\ArenaFactory;
use practice\world\WorldFactory;
use pocketmine\plugin\PluginBase;
use practice\entity\SplashPotion;
use practice\item\EnderPearlItem;
use practice\item\GoldenHeadItem;
use practice\database\mysql\MySQL;
use practice\database\mysql\Table;
use pocketmine\nbt\tag\CompoundTag;
use practice\item\SplashPotionItem;
use pocketmine\entity\EntityFactory;
use practice\kit\command\KitCommand;
use practice\session\SessionFactory;
use practice\duel\command\DuelCommand;
use pocketmine\entity\EntityDataHelper;
use practice\arena\command\ArenaCommand;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\SavedDataLoadingException;
use practice\command\PingCommand;
use practice\command\SpawnCommand;
use practice\database\mysql\queries\QueryAsync;
use practice\party\duel\DuelFactory as PartyDuelFactory;

final class Practice extends PluginBase {

    public const IS_DEVELOPING = false;

    static private ?self $instance = null;

    public static function getInstance(): self {
        return self::$instance;
    }

    protected function onLoad(): void {
        self::$instance = $this;
        MySQL::setCredentials($this->getConfig()->get('database'));
    }

    protected function onEnable(): void {
        $this->createTables();
        $this->registerEntities();
        $this->registerItems();
        $this->registerHandlers();
        $this->registerCommands();
        $this->unregisterCommands();

        ArenaFactory::loadAll();
        KitFactory::loadAll();
        SessionFactory::loadAll();
        WorldFactory::loadAll();

        DuelFactory::task();
        PartyDuelFactory::task();
        SessionFactory::task();
    }

    protected function onDisable(): void {
        ArenaFactory::saveAll();
        KitFactory::saveAll();
        SessionFactory::saveAll();
        WorldFactory::saveAll();

        DuelFactory::disable();
        PartyDuelFactory::disable();
    }

    protected function createTables(): void {
        MySQL::runAsync(new QueryAsync(Table::DUEL_STATS));
        MySQL::runAsync(new QueryAsync(Table::PLAYER_SETTINGS));
    }

    protected function registerEntities(): void {
        EntityFactory::getInstance()->register(EnderPearl::class, static function(World $world, CompoundTag $nbt): EnderPearl {
            return new EnderPearl(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityLegacyIds::ENDER_PEARL);

        EntityFactory::getInstance()->register(SplashPotion::class, static function(World $world, CompoundTag $nbt): SplashPotion {
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
            // Global
            new SpawnCommand,
            new PingCommand,
            // Arena
            new ArenaCommand,
            // Duel
            new DuelCommand,
            // Kit
            new KitCommand
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