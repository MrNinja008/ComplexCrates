<?php

namespace OguzhanUmutlu\ComplexCrates;

use pocketmine\block\TripwireHook;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\Player;

class EventListener implements Listener {
    private $plugin;
    private $createBreak = [];
    private $createItems = [];
    private $editBreak = [];
    private $editItems = [];
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
    }
    public function setCreateBreakPlayer($playerName, string $crateName) {
        if($playerName instanceof Player) $playerName = $playerName->getName();
        $this->createBreak[$playerName] = $crateName;
    }
    public function setEditBreakPlayer($playerName, string $crateName) {
        if($playerName instanceof Player) $playerName = $playerName->getName();
        $this->editBreak[$playerName] = $crateName;
    }
    public function onBlockBreak(BlockBreakEvent $e) {
        $crates = $this->plugin->getCrateManager()->getAllCrates();
        $cratePos = array_map(function($n){return $n["pos"];},$crates);
        $block = $e->getBlock();
        $blockPos = ["x" => $block->getX(), "y" => $block->getY(), "z" => $block->getZ(), "level" => $block->getLevel()->getFolderName()];
        if(in_array($blockPos, $cratePos)) {
            $e->setCancelled(true);
        }
        $player = $e->getPlayer();
        if(isset($this->createBreak[$player->getName()])) {
            $this->createItems[$player->getName()] = [
                "name" => $this->createBreak[$player->getName()],
                "block" => $e->getBlock()
            ];
            unset($this->createBreak[$player->getName()]);
            $e->setCancelled(true);
            $player->sendMessage($this->plugin->getLanguageManager()->translate("command-create-itemcheck"));
        } else if(isset($this->editBreak[$player->getName()])) {
            $this->createItems[$player->getName()] = [
                "name" => $this->editBreak[$player->getName()],
                "block" => $e->getBlock()
            ];
            unset($this->editBreak[$player->getName()]);
            $e->setCancelled(true);
            $player->sendMessage($this->plugin->getLanguageManager()->translate("command-edit-itemcheck"));
        }
    }
    private $cratedat = [];
    public function onInteract(PlayerInteractEvent $e) {
        $crates = $this->plugin->getCrateManager()->getAllCrates();
        $cratePos = array_map(function($n){return $n["pos"];},$crates);
        $player = $e->getPlayer();
        if(in_array($e->getAction(), [2,3,4])) return;
        $block = $e->getBlock();
        $blockPos = ["x" => $block->getX(), "y" => $block->getY(), "z" => $block->getZ(), "level" => $block->getLevel()->getFolderName()];
        if(in_array($blockPos, $cratePos)) {
            $e->setCancelled(true);
            $crate = $crates[array_search($blockPos, $cratePos)];
            $items = array_map(function($n){
                $item = Item::get($n["id"], $n["meta"], $n["count"]);
                $item->setCompoundTag($n["nbt"]);
                return $item;
            }, $crate["items"]);
            if($e->getItem()->getId() != 131 || !is_array($e->getItem()->getLore()) || !isset($e->getItem()->getLore()[0]) || $e->getItem()->getLore()[0] != str_replace("%0", $crate["name"], $this->plugin->getLanguageManager()->translate("item-lore")) || $e->getItem()->getCustomName() != str_replace("%0", $crate["name"], $this->plugin->getLanguageManager()->translate("item-name"))) {
                $this->plugin->getMenuManager()->previewCrate($items, $player);
                return;
            }
            if(!$this->plugin->getConfig()->getNested("crate-access") && isset($this->cratedat[$crate["name"]])) {
                $player->sendMessage($this->plugin->getLanguageManager()->translate("someone-is-using", ["%0" => $this->cratedat[$crate["name"]], "%1" => $crate["name"]]));
                return;
            }
            if($this->plugin->getMenuManager()->getPlayerDat($player)) {
                $player->sendMessage($this->plugin->getLanguageManager()->translate("already-using"));
                return;
            }
            $this->cratedat[$crate["name"]] = $player->getName();
            $player->getInventory()->removeItem($this->plugin->getCrateManager()->getKey($crate["name"]));
            $this->plugin->getMenuManager()->start($this->plugin->getMenuManager()->create(), $items, $player);
        }
    }
    public function onChat(PlayerChatEvent $e) {
        $player = $e->getPlayer();
        $message = $e->getMessage();
        if($message == "\$done" && isset($this->createItems[$player->getName()])) {
            $a = $this->createItems[$player->getName()];
            $player->sendMessage($this->plugin->getCrateManager()->createCrate(
                $a["name"],
                $a["block"],
                array_map(function($a){
                    return [
                        "id" => $a->getId(),
                        "meta" => $a->getDamage(),
                        "count" => $a->getCount(),
                        "nbt" => $a->getCompoundTag()
                    ];
                }, $player->getInventory()->getContents())
            ));
            $e->setCancelled(true);
            unset($this->createItems[$player->getName()]);
        } else if($message == "\$done" && isset($this->editItems[$player->getName()])) {
            $a = $this->editItems[$player->getName()];
            $player->sendMessage($this->plugin->getCrateManager()->editCrate(
                $a["name"],
                $a["block"],
                array_map(function($a){
                    return [
                        "id" => $a->getId(),
                        "meta" => $a->getDamage(),
                        "count" => $a->getCount(),
                        "nbt" => $a->getCompoundTag()
                    ];
                }, $player->getInventory()->getContents())
            ));
            $e->setCancelled(true);
            unset($this->createItems[$player->getName()]);
        } else if($message == "\$cancelcrate" && (isset($this->createBreak[$player->getName()]) || isset($this->createItems[$player->getName()]) || isset($this->editBreak[$player->getName()]) || isset($this->editItems[$player->getName()]))) {
            if(isset($this->createBreak[$player->getName()])) unset($this->createBreak[$player->getName()]);
            if(isset($this->createItems[$player->getName()])) unset($this->createItems[$player->getName()]);
            if(isset($this->editBreak[$player->getName()])) unset($this->editBreak[$player->getName()]);
            if(isset($this->editItems[$player->getName()])) unset($this->editItems[$player->getName()]);
            $e->setCancelled(true);
            $player->sendMessage($this->plugin->getLanguageManager()->translate("command-cancel"));
        }
    }
}