<?php
# MADE BY:
#  __    __                                          __        __  __  __                     
# /  |  /  |                                        /  |      /  |/  |/  |                    
# $$ |  $$ |  ______   _______    ______    ______  $$ |____  $$/ $$ |$$/   _______  __    __ 
# $$  \/$$/  /      \ /       \  /      \  /      \ $$      \ /  |$$ |/  | /       |/  |  /  |
#  $$  $$<  /$$$$$$  |$$$$$$$  |/$$$$$$  |/$$$$$$  |$$$$$$$  |$$ |$$ |$$ |/$$$$$$$/ $$ |  $$ |
#   $$$$  \ $$    $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |$$ |$$ |$$ |      $$ |  $$ |
#  $$ /$$  |$$$$$$$$/ $$ |  $$ |$$ \__$$ |$$ |__$$ |$$ |  $$ |$$ |$$ |$$ |$$ \_____ $$ \__$$ |
# $$ |  $$ |$$       |$$ |  $$ |$$    $$/ $$    $$/ $$ |  $$ |$$ |$$ |$$ |$$       |$$    $$ |
# $$/   $$/  $$$$$$$/ $$/   $$/  $$$$$$/  $$$$$$$/  $$/   $$/ $$/ $$/ $$/  $$$$$$$/  $$$$$$$ |
#                                         $$ |                                      /  \__$$ |
#                                         $$ |                                      $$    $$/ 
#                                         $$/                                        $$$$$$/                       

namespace Xenophilicy\Syncount\Command;

use pocketmine\command\{CommandSender, PluginCommand};
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Syncount\Syncount;

/**
 * Class SyncountCommand
 * @package Xenophilicy\Syncount\Command
 */
class SyncountCommand extends PluginCommand {
    
    /**
     * @var Syncount
     */
    private $plugin;
    
    /**
     * @param string $name
     * @param Syncount $plugin
     */
    public function __construct(string $name, Syncount $plugin){
        parent::__construct($name, $plugin);
        $this->plugin = $plugin;
        $this->setDescription("Add or remove synced servers");
        $this->setPermission("syncount");
        $this->setAliases(["sc"]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if($sender->hasPermission("syncount.add") || $sender->hasPermission("syncount.del")){
            if(count($args) < 2){
                $sender->sendMessage(TF::RED . "Usage: /sc <add|del> <ip> [port]");
                return false;
            }
            $port = isset($args[2]) ? $args[2] : 19132;
            switch($args[0]){
                case "add":
                case "new":
                    if(isset($this->plugin->queryResults[$args[1] . ":" . $port])){
                        $sender->sendMessage(TF::YELLOW . "Server " . TF::AQUA . $args[1] . ":" . $port . TF::YELLOW . " is already added");
                        return true;
                    }
                    $this->plugin->queryResults[$args[1] . ":" . $port] = [0, 0];
                    $this->plugin->startQueryTask($args[1], $port);
                    $servers = $this->plugin->config->get("Servers");
                    array_push($servers, $args[1] . ":" . $port);
                    $this->plugin->config->set("Servers", $servers);
                    $this->plugin->config->save();
                    $sender->sendMessage(TF::GREEN . "Server " . TF::AQUA . $args[1] . ":" . $port . TF::GREEN . " has been added");
                    break;
                case "rm":
                case "rem":
                case "del":
                case "remove":
                case "delete":
                    if(!isset($this->plugin->queryResults[$args[1] . ":" . $port])){
                        $sender->sendMessage(TF::RED . "Server " . TF::AQUA . $args[1] . ":" . $port . TF::RED . " is not added");
                        return true;
                    }
                    unset($this->plugin->queryResults[$args[1] . ":" . $args[2]]);
                    $servers = $this->plugin->config->get("Servers");
                    unset($servers[$args[1] . ":" . $port]);
                    $this->plugin->config->set("Servers", $servers);
                    $this->plugin->config->save();
                    $sender->sendMessage(TF::GREEN . "Server " . TF::AQUA . $args[1] . ":" . $port . TF::GREEN . " has been removed");
                    break;
                default:
                    $sender->sendMessage(TF::RED . "Usage: /sc <add|rem> <ip> [port]");
            }
        }else{
            $sender->sendMessage(TF::RED . "You don't have permission to use syncount");
        }
        return true;
    }
}