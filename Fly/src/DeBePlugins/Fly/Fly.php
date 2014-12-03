<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\Fly;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerKickEvent;

class Fly extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$ik = $this->isKorean();
		$fly = $this->fly;
		switch(strtolower($cmd)){
			case "fly":
				if($fly["Fly"]) $b = false;
				else $b = true;
				$fly["Fly"] = $b;
				$m = "[Fly]" . ($b ? ($ik ? "플라이 킥을 켭니다." : "Fly kick is On") : ($ik ? "플라이킥을 끕니다." : "Fly kick is Off"));
			break;
		}
		$this->fly = $fly;
		$this->saveYml();
		$this->getServer()->broadCastMessage($m);
		return true;
	}

	public function onPlayerKick(PlayerKickEvent $event){
		if($this->fly["Fly"] && $event->getReason() == "Flying is not enabled on this server") $event->setCancelled();
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->fly = (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "Fly.yml", Config::YAML,["Fly" => true]))->getAll();
	}

	public function saveYml(){
		$fly = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "Fly.yml", Config::YAML);
		$fly->setAll($this->fly);
		$fly->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}