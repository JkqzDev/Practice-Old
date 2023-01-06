<?php

declare(strict_types=1);

namespace practice\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class WhoCommand extends Command {

    public function __construct() {
        parent::__construct('who', 'Use command for view info player');
        $this->setPermission('who.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::colorize('&cUse /who [player]'));
            return;
        }
        $player = $sender->getServer()->getPlayerByPrefix($args[0]);

        if (!$player instanceof Player) {
            $sender->sendMessage(TextFormat::colorize('&cPlayer is offline.'));
            return;
        }
        $sender->sendMessage(TextFormat::colorize(
            '&e' . $player->getName() . '\'s Information' . PHP_EOL .
            '&ePlayer Address: &f' . $player->getNetworkSession()->getIp() . PHP_EOL .
            '&ePlayer Device OS: &f' . $player->getPlayerInfo()->getExtraData()['DeviceOS'] . PHP_EOL .
            '&ePlayer Device Input: &f' . $player->getPlayerInfo()->getExtraData()['CurrentInputMode']
        ));
    }
}