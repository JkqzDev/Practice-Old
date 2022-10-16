<?php

declare(strict_types=1);

namespace practice\session\handler;

trait HandlerTrait {
    
    private ?SetupArenaHandler $setupArenaHandler = null;
    private ?SetupDuelHandler $setupDuelHandler = null;
    
    public function getSetupArenaHandler(): ?SetupArenaHandler {
        return $this->setupArenaHandler;
    }
    
    public function getSetupDuelHandler(): ?SetupDuelHandler {
        return $this->setupDuelHandler;
    }
    
    public function startSetupArenaHandler(): void {
        $this->setupArenaHandler = new SetupArenaHandler;
    }

    public function startSetupDuelHandler(): void {
        $this->setupDuelHandler = new SetupDuelHandler;
    }

    public function stopSetupArenaHandler(): void {
        $this->setupArenaHandler = null;
    }

    public function stopSetupDuelHandler(): void {
        $this->setupDuelHandler = null;
    }
}