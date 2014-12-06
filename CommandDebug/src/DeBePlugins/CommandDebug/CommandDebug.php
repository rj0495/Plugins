<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CommandDebug;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class CommandDebug extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onServerCommand(ServerCommandEvent $event){
		$this->debug($event->getCommand(), $event->getSender());
	}

	public function onRemoteServerCommand(RemoteServerCommandEvent $event){
		$this->debug($event->getCommand(), $event->getSender());
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$this->debugMessage($event);
	}

	public function debugMessage($event){
		$cmd = $event->getMessage();
		if(strpos($cmd, "/") !== 0) return false;
		$this->debug(substr($cmd, 1, strlen($cmd)), $event->getPlayer());
	}

	public function debug($cmd, $sender){
		$name = $sender->getName();
		$color = "";
		if($name !== "CONSOLE" && $name !== "RCON" && !$sender->isOp()) $color = TextFormat::BLUE;
		$this->getLogger()->info($color . "$name : $cmd");
	}
}