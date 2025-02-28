<?php

namespace Mrchlldev\BetterProtected;

use Mrchlldev\BetterProtected\Loader;

use pocketmine\block\Grass;
use pocketmine\block\Wood;
use pocketmine\block\WoodenTrapdoor;

use pocketmine\item\Axe;
use pocketmine\item\Shovel;
use pocketmine\item\Hoe;
use pocketmine\block\ItemFrame;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\server\CommandEvent;

class EventListener implements Listener {

    public Loader $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        $world_name = $player->getWorld()->getFolderName();
        if ($this->plugin->getWorldData()->get($world_name) === null) return;
        if ($this->plugin->getWorldData()->getNested($world_name . ".world_name") === $world_name) {
            if (!$this->plugin->getWorldData()->getNested($world_name . ".locked_world")) return;
            if ($this->plugin->getWorldData()->getNested($world_name . ".interact")) return;
            if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                if ($block instanceof ItemFrame && $block instanceof WoodenTrapdoor) {
                    $player->sendMessage(Loader::PREFIX . "§cYou can't interact that block in this world!");
                    $event->cancel(); 
                } else if ($item instanceof Axe && $item instanceof Shovel && $item instanceof Hoe) {
                    if ($block instanceof Grass && $block instanceof Wood) {
                        $player->sendMessage(Loader::PREFIX . "§cYou can't interact that block in this world!");
                        $event->cancel();
                    }
                }
                $player->sendMessage(Loader::PREFIX . "§cYou can't interact in this world!");
                $event->cancel();
            } else if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                $player->sendMessage(Loader::PREFIX . "§cYou can't interact in this world!");
                $event->cancel();
            }
        }
    }

    public function onToggleFlight(PlayerToggleFlightEvent $event): void {
        $player = $event->getPlayer();
        $world_name = $player->getWorld()->getFolderName();
        if ($this->plugin->getWorldData()->get($world_name) === null) return;
        if ($this->plugin->getWorldData()->getNested($world_name . ".world_name") === $world_name) {
            if (!$this->plugin->getWorldData()->getNested($world_name . ".locked_world")) return;
            if ($this->plugin->getWorldData()->getNested($world_name . ".fly_mode")) return;
            $player->sendMessage(Loader::PREFIX . "§cYou can't fly in this world!");
            $event->cancel();
        }
    }

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $world_name = $player->getWorld()->getFolderName();
        if ($this->plugin->getWorldData()->get($world_name) === null) return;
        if ($this->plugin->getWorldData()->getNested($world_name . ".world_name") === $world_name) {
            if (!$this->plugin->getWorldData()->getNested($world_name . ".locked_world")) return;
            if ($this->plugin->getWorldData()->getNested($world_name . ".break_block")) return;
            $player->sendMessage(Loader::PREFIX . "§cYou can't break that block in this world!");
            $event->cancel();
        }
    }

    public function onPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $world_name = $player->getWorld()->getFolderName();
        if ($this->plugin->getWorldData()->get($world_name) === null) return;
        if ($this->plugin->getWorldData()->getNested($world_name . ".world_name") === $world_name) {
            if (!$this->plugin->getWorldData()->getNested($world_name . ".locked_world")) return;
            if ($this->plugin->getWorldData()->getNested($world_name . ".place_block")) return;
            $player->sendMessage(Loader::PREFIX . "§cYou can't place that block in this world!");
            $event->cancel();
        }
    }

    public function onDrop(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        $world_name = $player->getWorld()->getFolderName();
        if ($this->plugin->getWorldData()->get($world_name) === null) return;
        if ($this->plugin->getWorldData()->getNested($world_name . ".world_name") === $world_name) return;
            if ($this->plugin->getWorldData()->getNested($world_name . ".locked_world")) {
            if ($this->plugin->getWorldData()->getNested($world_name . ".drop_item")) return;
            $player->sendMessage(Loader::PREFIX . "§cYou can't drop that item in this world!");
            $event->cancel();
        }
    }

    public function onDamage(EntityDamageEvent $event): void {
        if(!($player = $event->getEntity()) instanceof Player) return;
        $world_name = $player->getWorld()->getFolderName();
        if ($this->plugin->getWorldData()->get($world_name) === null) return;
        if ($this->plugin->getWorldData()->getNested($world_name . ".world_name") === $world_name) {
            if (!$this->plugin->getWorldData()->getNested($world_name . ".locked_world")) return;
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_FALL:
                    if ($this->plugin->getWorldData()->getNested($world_name . ".fall_damage")) return;
                    $player->sendTip("§aThe system has disable fall damage to you!");
                    $event->cancel();
                break;
                case EntityDamageEvent::CAUSE_VOID:
                    if ($this->plugin->getWorldData()->getNested($world_name . ".void_damage")) return;
                    $player->sendMessage("§aThe system has disable void damage to you. If you want to back to spawn, use command §e/spawn §aor §e/lobby");
                    $event->cancel();
                break;
                case EntityDamageEvent::CAUSE_FIRE:
                case EntityDamageEvent::CAUSE_FIRE_TICK:
                case EntityDamageEvent::CAUSE_MAGIC:
                    if ($this->plugin->getWorldData()->getNested($world_name . ".more_damage")) return;
                    $event->cancel();
                break;
            }
            if ($event instanceof EntityDamageByEntityEvent) {
                if(!($damager = $event->getDamager()) instanceof Player) return;
                if ($this->plugin->getWorldData()->getNested($world_name . ".pvp")) return;
                $damager->sendMessage(Loader::PREFIX . "§cYou can't pvp in this world!");
                $event->cancel();
            } else if ($event instanceof EntityDamageByBlockEvent) {
                if ($this->plugin->getWorldData()->getNested($world_name . ".block_damage")) return;
                $event->cancel();
            }
        }
    }
}