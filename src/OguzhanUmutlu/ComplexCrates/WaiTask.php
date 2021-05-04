<?php

namespace OguzhanUmutlu\ComplexCrates;
use pocketmine\level\sound\FizzSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class WaiTask extends Task {
    private $menu;
    private $items;
    private $player;
    private $crate;
    private $repeat;
    private $th;
    public function __construct($menu, $items, Player $player, $crate, MenuManager $th) {
        $this->menu = $menu;
        $this->items = $items;
        $this->player = $player;
        $this->crate = $crate;
        $this->repeat = 0;
        $this->th = $th;
    }
    public function onRun(int $currentTick) {
        if($this->repeat > 10 || !$this->player) {
            $this->getHandler()->cancel();
            return;
        }
        $this->th->change($this->menu, $this->items);
        $this->repeat++;
        if($this->repeat == 10) {
            $this->player->getInventory()->addItem($this->menu->getInventory()->getItem(13));
            $this->th->unsetPlayerDat($this->player);
            $this->th->getPlugin()->getEventListener()->delCrateDat($this->crate["name"]);
            $this->player->removeWindow($this->menu->getInventory());
        }
    }
}
