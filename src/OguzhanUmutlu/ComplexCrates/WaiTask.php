<?php

namespace OguzhanUmutlu\ComplexCrates;
use pocketmine\scheduler\Task;

class WaiTask extends Task {
    public $function;
    public function __construct(callable $function) {
        $this->function = $function;
    }
    public function onRun(int $currentTick) {
        ($this->function)();
    }
}