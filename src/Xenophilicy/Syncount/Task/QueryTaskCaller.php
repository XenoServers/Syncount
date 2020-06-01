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

namespace Xenophilicy\Syncount\Task;

use pocketmine\scheduler\Task;
use Xenophilicy\Syncount\Syncount;

/**
 * Class QueryTaskCaller
 * @package Xenophilicy\Syncount\Task
 */
class QueryTaskCaller extends Task {
    
    private $plugin;
    private $host;
    private $port;
    
    /**
     * QueryTaskCaller constructor.
     * @param Syncount $plugin
     * @param string $host
     * @param int $port
     */
    public function __construct(Syncount $plugin, string $host, int $port){
        $this->plugin = $plugin;
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick){
        $this->plugin->getServer()->getAsyncPool()->submitTask(new QueryTask($this->host, $this->port));
    }
}