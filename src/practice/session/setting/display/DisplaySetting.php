<?php

declare(strict_types=1);

namespace practice\session\setting\display;

use practice\session\Session;
use practice\session\setting\Setting;

class DisplaySetting extends Setting {
    
    public function __construct(string $name, bool $value = true) {
        parent::__construct($name, $value);
    }
    
    public function isEnabled(): bool {
        return $this->value;
    }
    
    public function setEnabled(bool $value): void {
        $this->value = $value;
    }
    
    abstract public function execute(Session $session): void;
}