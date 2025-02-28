<?php

namespace Mrchlldev\BetterProtected;

use Mrchlldev\BetterProtected\EventListener;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Loader extends PluginBase {

    public Config $world_data;

    public const PREFIX = "§l§6Better§gProtect §r§7» §r";

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->world_data = new Config($this->getDataFolder() . "world-protected.json", Config::JSON);
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $aliasUsed
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $aliasUsed, array $args): bool {
        if ($command->getName() === "betterprotect") {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "help":
                        $sender->sendMessage(
                            "§a» /" . $aliasUsed . " open §e(for opened world)" . 
                            "\n§a» /" . $aliasUsed . " lock §e(for locked world)" . 
                            "\n§a» /" . $aliasUsed . " protect §e(for protect world)" . 
                            "\n§a» /" . $aliasUsed . " pvp [string:world name] [enable:disable] §e(for manage pvp in world)" . 
                            "\n§a» /" . $aliasUsed . " interact [string:world name] [enable:disable] §e(for manage interact in world)" . 
                            "\n§a» /" . $aliasUsed . " fall_damage [string:world name] [enable:disable] §e(for manage fall damage in world)" . 
                            "\n§a» /" . $aliasUsed . " block_damage [string:world name] [enable:disable] §e(for manage block damage like cactus in world)" . 
                            "\n§a» /" . $aliasUsed . " void_damage [string:world name] [enable:disable] §e(for manage void damage in world)" . 
                            "\n§a» /" . $aliasUsed . " more_damage [string:world name] [enable:disable] §e(for manage more damage like lava, magic in world)" . 
                            "\n§a» /" . $aliasUsed . " fly_mode [string:world name] [enable:disable] §e(for manage flying mode in world)" . 
                            "\n§a» /" . $aliasUsed . " break_block [string:world name] [enable:disable] §e(for manage break block in world)" . 
                            "\n§a» /" . $aliasUsed . " place_block [string:world name] [enable:disable] §e(for manage place block in world)"
                        );
                        return true;
                    break;
                    case "open":
                        $world_name = null;
                        if ($sender instanceof Player) {
                            $world_name = $sender->getWorld()->getFolderName();
                        } else {
                            if (!isset($args[1])) {
                                $sender->sendMessage("§eFor console, use /" . $aliasUsed . " open [string:world name]");
                                return false;
                            }
                            if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                                $sender->sendMessage("§cWorld by that name not found!");
                                return false;
                            }
                            $world_name = $args[1];
                            if (!$this->isProtected($world_name)) {
                                $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                                return false;
                            }
                        }
                        $this->updateWorld($world_name, "locked_world", false);
                        $sender->sendMessage("§aSuccesfully opened the world.");
                    break;
                    case "lock":
                        $world_name = null;
                        if ($sender instanceof Player) {
                            $world_name = $sender->getWorld()->getFolderName();
                        } else {
                            if (!isset($args[1])) {
                                $sender->sendMessage("§eFor console, use /" . $aliasUsed . " lock [string:world name]");
                                return false;
                            }
                            if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                                $sender->sendMessage("§cWorld by that name not found!");
                                return false;
                            }
                            $world_name = $args[1];
                            if (!$this->isProtected($world_name)) {
                                $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                                return false;
                            }
                        }
                        $this->updateWorld($world_name, "locked_world", true);
                        $sender->sendMessage("§aSuccesfully locked the world.");
                    break;
                    case "protect":
                        $world_name = null;
                        if ($sender instanceof Player) {
                            $world_name = $sender->getWorld()->getFolderName();
                        } else {
                            if (!isset($args[1])) {
                                $sender->sendMessage("§eFor console, use /" . $aliasUsed . " protect [string:world name]");
                                return false;
                            }
                            if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                                $sender->sendMessage("§cWorld by that name not found!");
                                return false;
                            }
                            $world_name = $args[1];
                        }
                        if ($this->isProtected($world_name)) {
                            $sender->sendMessage("§cThat world already protected!");
                            return false;
                        }
                        $this->protectWorld($world_name);
                        $sender->sendMessage(self::PREFIX . "§aSuccesfully protect that world!");
                        return true;
                    break;
                    case "break_block":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled break block in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled break block in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "place_block":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled place block in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled place block in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "pvp":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled pvp in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled pvp in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "fly_mode":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled flying in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled flying in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "fall_damage":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled fall damage in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled fall damage in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "void_damage":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled void damage in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled void damage in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "block_damage":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled block damage in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled block damage in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "more_damage":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled more damage in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled more damage in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                    case "interact":
                        if (!isset($args[1]) && !isset($args[2])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        if ($this->getServer()->getWorldManager()->getWorldByName($args[1]) === null) {
                            $sender->sendMessage("§cWorld by that name not found!");
                            return false;
                        }
                        if (!$this->isProtected($args[1])) {
                            $sender->sendMessage("§cThis world's not protected. Please use command §e/" . $aliasUsed . " protect §cto protect world!");
                            return false;
                        }
                        if (!in_array($args[2], ["enable", "disable"])) {
                            $sender->sendMessage("§cInvalid command. Use §e/" . $aliasUsed . " help §cto get all command usage!");
                            return false;
                        }
                        switch ($args[2]) {
                            case "enable":
                                $this->updateWorld($args[1], $args[0], false);
                                $sender->sendMessage("§aSuccesfully enabled interact in: §e" . $args[1]);
                                return true;
                            break;
                            case "disable":
                                $this->updateWorld($args[1], $args[0], true);
                                $sender->sendMessage("§aSuccesfully disabled interact in: §e" . $args[1]);
                                return true;
                            break;
                        }
                    break;
                }
            } else {
                $sender->sendMessage("§cUsage: §f/" . $aliasUsed . " help");
                return false;
            }
        }
        return false;
    }

    /**
     * Protect & save world
     * @param string $world_name
     * @return void
     */
    public function protectWorld(string $world_name): void {
        $data = [
            "world_name" => $world_name,
            "locked_world" => true,
            "interact" => false,
            "fall_damage" => false,
            "block_damage" => false,
            "void_damage" => false,
            "more_damage" => false,
            "pvp" => false,
            "fly_mode" => true,
            "break_block" => false,
            "place_block" => false,
            "drop_item" => true
        ];
        $this->world_data->setNested($world_name, $data);
        $this->world_data->save();
        $this->world_data->reload();
    }

    /**
     * Checks whether the world name is protected or not.
     * @param string $world_name
     */
    public function isProtected(string $world_name): bool {
        if ($this->world_data->getNested($world_name . ".world_name") !== null) {
            return true;
        }
        return false;
    }

    /**
     * For update world config
     * @param string $world_name
     * @param string $key
     * @param string|bool|array $value
     * @return void
     */
    public function updateWorld(string $world_name, string $key, string|bool|array $value): void {
        $this->world_data->setNested($world_name . "." . $key, $value);
        $this->world_data->save();
        $this->world_data->reload();
    }

    /**
     * @return Config
     */
    public function getWorldData(): Config {
        return $this->world_data;
    }
}