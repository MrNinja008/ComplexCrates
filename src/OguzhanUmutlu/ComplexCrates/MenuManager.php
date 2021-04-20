<?php


namespace OguzhanUmutlu\ComplexCrates;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;

class MenuManager {
    private $plugin;
    private $playerdat = [];
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
    }
    public function create(): ?InvMenu {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        for($i=0;$i<27;$i++){
            if($i != 13)$menu->getInventory()->setItem($i, new Item(ItemIds::STAINED_GLASS_PANE, rand(0, 15)));
        }
        $menu->setName(" ");
        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) : void {}));
        $menu->setInventoryCloseListener(function(Player $player, InvMenuInventory $inventory)use($menu){
            if(isset($this->playerdat[$player->getName()])) {
                $menu->send($player);
            }
        });
        return $menu;
    }
    public function previewCrate(array $items, Player $player) {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->getInventory()->setContents($items);
        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) : void {}));
        $menu->setName(" ");
        $menu->send($player);
    }

    public function start(InvMenu $menu, array $items, Player $player, $crate) {
        $menu->send($player);
        $this->playerdat[$player->getName()] = true;
        $this->plugin->getScheduler()->scheduleRepeatingTask(new WaiTask($menu, $items, $player, $crate, $this), 5);
    }
    public function change(InvMenu $menu, array $items): InvMenu {
        for($i=0;$i<27;$i++){
            if($i != 13)$menu->getInventory()->setItem($i, new Item(ItemIds::STAINED_GLASS_PANE, rand(0, 15)));
        }
        $menu->getInventory()->setItem(13, $items[array_rand($items)]);
        return $menu;
    }
    public function getPlayerDat($player): bool {
        if($player instanceof Player) $player = $player->getName();
        return isset($this->playerdat[$player]);
    }
    public function unsetPlayerDat($player) {
        if($player instanceof Player) $player = $player->getName();
        if($this->getPlayerDat($player)) unset($this->playerdat[$player]);
    }
    public function getPlugin(): ComplexCrates {
        return $this->plugin;
    }
}