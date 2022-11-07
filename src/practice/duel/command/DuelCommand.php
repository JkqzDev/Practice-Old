<?php

declare(strict_types=1);

namespace practice\duel\command;

use pocketmine\player\Player;
use pocketmine\command\Command;
use practice\session\SessionFactory;
use practice\duel\queue\QueueFactory;
use pocketmine\command\CommandSender;
use practice\form\duel\manage\SetupDuelForm;
use practice\form\duel\manage\DeleteDuelForm;

final class DuelCommand extends Command {

    public function __construct() {
        parent::__construct('duel', 'Duel command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null || !isset($args[0])) {
            return;
        }

        if ($sender->hasPermission('duel.command')) {
            $subCommand = strtolower($args[0]);

            if ($subCommand === 'setup') {
                $form = new SetupDuelForm;
                $sender->sendForm($form);
            } elseif ($subCommand === 'delete') {
                $form = new DeleteDuelForm;
                $sender->sendForm($form);
            }
        }
    }
}