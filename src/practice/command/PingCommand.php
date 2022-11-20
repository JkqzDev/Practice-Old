<?php

declare(strict_types=1);

namespace practice\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class PingCommand extends Command {

    public function __construct() {
        parent::__construct('ping', 'Command for player ping');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cYour ping: &f' . $sender->getNetworkSession()->getPing()));
            return;
        }
        $player = $sender->getServer()->getPlayerByPrefix($args[0]);

        if (!$player instanceof Player) {
            $sender->sendMessage(TextFormat::colorize('&cPlayer offline.'));
            return;
        }
        $sender->sendMessage(TextFormat::colorize('&c' . $player->getName() . '\'s ping: &f' . $player->getNetworkSession()->getPing()));
    }
}