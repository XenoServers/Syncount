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

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Xenophilicy\Syncount\Syncount;

/**
 * Class QueryTask
 * @package Xenophilicy\Syncount\Task
 */
class QueryTask extends AsyncTask {
    
    private $host;
    private $port;
    private $pluginList;
    
    /**
     * QueryTask constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port){
        $this->host = $host;
        $this->port = $port;
        $this->pluginList = "";
    }
    
    public function onRun(){
        $queryServer = $this->sendQuery($this->host, (int)$this->port);
        $status = $queryServer === null ? 'offline' : 'online';
        if($status == "online" && count($queryServer) >= 16){
            $this->setResult([$queryServer[15], $queryServer[17]]);
        }else{
            $this->setResult([0, 0]);
        }
        
    }
    
    /**
     * @param string $host
     * @param int $port
     * @return false|string[]|null
     */
    private function sendQuery(string $host, int $port){
        $timeout = 1;
        $socket = @fsockopen("udp://" . $host, $port, $timeout);
        if(!$socket) return null;
        stream_set_timeout($socket, 1);
        $online = @fwrite($socket, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01");
        if(!$online) return null;
        $challenge = @fread($socket, 1400);
        $res = stream_get_meta_data($socket);
        if($res['timed_out']) return null;
        if(!$challenge) return null;
        $challenge = substr(preg_replace("/[^0-9-]/si", "", $challenge), 1);
        $query = sprintf("\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01", $challenge >> 24, $challenge >> 16, $challenge >> 8, $challenge >> 0);
        if(!@fwrite($socket, $query)) return null;
        $response = [];
        $response[] = @fread($socket, 2048);
        $response = implode($response);
        $response = substr($response, 16);
        $response = explode("\0", $response);
        array_pop($response);
        array_pop($response);
        @fclose($socket);
        return $response;
    }
    
    // This is an edited GitHub Gist by xBeastMode → https://gist.github.com/xBeastMode/89a9d85c21ec5f42f14db49550ea8e5c
    
    /**
     * @param Server $server
     * @return void
     */
    public function onCompletion(Server $server){
        if(!Syncount::getPlugin()->isDisabled()){
            Syncount::getPlugin()->queryTaskCallback($this->getResult(), $this->host, $this->port, $this->pluginList);
        }
    }
}