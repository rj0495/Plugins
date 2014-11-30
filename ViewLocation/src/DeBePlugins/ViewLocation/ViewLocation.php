<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ViewLocation;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class ViewLocation extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$rm = "Usage: /ViewLocation ";
		$mm = "[ViewLocation] ";
		if(!isset($sub[0])){
			if($sender->getName() == "CONSOLE"){
				$sender->sendMessage($this->isKorean() ? $rm . "<플레이어명>": $rm . "<PlayerName>");
				return true;
			}else $p = $sender;
		}
		if(!isset($p)) $p = $this->getServer()->getPlayer(strtolower($sub[0]));
		if($p == null){
			$sender->sendMessage($this->isKorean() ? $mm . "$sub[0] 는 잘못된 플레이어명입니다.": $mm . "$sub[0] is invalid player");
		}else{
			$sender->sendMessage($mm.$p->getName().($this->isKorean() ? " 님의 좌표" : "\'s Location")."::  X: ".$p->getfloorX()." Y: ".$p->getfloorY()." Z:".$p->getfloorZ());
		}
		return true;
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}