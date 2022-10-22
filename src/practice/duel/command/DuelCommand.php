<?php

declare(strict_types=1);

namespace practice\duel\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\form\duel\manage\DeleteDuelForm;
use practice\form\duel\manage\SetupDuelForm;
use practice\session\SessionFactory;

final class DuelCommand extends Command {

    public function __construct() {
        parent::__construct('duel', 'Duel command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null) {
            return;
        }
        
        if (!isset($args[0])) {
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