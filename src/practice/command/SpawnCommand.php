<?php

declare(strict_types=1);

namespace practice\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\session\SessionFactory;

final class SpawnCommand extends Command {

    public function __construct() {
        parent::__construct('spawn', 'Command for teleport to spawn', null, ['hub']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null) {
            return;
        }

        if (!$session->inArena()) {
            $sender->sendMessage(TextFormat::colorize('&cYou cannot use this command'));
            return;
        }
        $arena = $session->getArena();

        if ($arena->inCombat($sender)) {
            $sender->sendMessage(TextFormat::colorize('&cYou have combat tag'));
            return;
        }

        if ($session->getEnderpearl() !== null) {
            $sender->sendMessage(TextFormat::colorize('&cYou have enderpearl cooldown'));
            return;
        }
        $arena->quit($sender);
    }
}