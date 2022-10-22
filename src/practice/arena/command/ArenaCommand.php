<?php

declare(strict_types=1);

namespace practice\arena\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\form\arena\manage\DeleteArenaForm;
use practice\form\arena\manage\SetupArenaForm;
use practice\session\SessionFactory;

final class ArenaCommand extends Command {

    public function __construct() {
        parent::__construct('arena', 'Arena command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null) {
            return;
        }

        if (!$sender->hasPermission('arena.command') || !isset($args[0])) {
            if ($session->inArena()) {
                $arena = $session->getArena();

                if ($arena->inCombat($sender)) {
                    $sender->sendMessage(TextFormat::colorize('&cYou have combat tag'));
                    return;
                }
                $arena->quit($sender);
            }
            return;
        }
        $subCommand = strtolower($args[0]);

        if ($subCommand === 'setup') {
            $form = new SetupArenaForm;
            $sender->sendForm($form);
        } elseif ($subCommand === 'delete') {
            $form = new DeleteArenaForm;
            $sender->sendForm($form);
        }
    }
}