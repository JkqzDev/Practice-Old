<?php

declare(strict_types=1);

namespace practice\duel\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\queue\QueueFactory;
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
        
        if (is_numeric($args[0])) {
            $queue = QueueFactory::get($sender);
            
            if ($queue === null) {
                QueueFactory::create($sender, intval($args[0]));
                $sender->sendMessage('You have joined to queue type ' . intval($args[0]));
                return;
            }
            QueueFactory::remove($sender);
            $sender->sendMessage('You have left to queue');
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