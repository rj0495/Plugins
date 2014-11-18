<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\SubInventory;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\item\Item;

class SubInventory extends PluginBase{

	public function onEnable(){
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$n = $sender->getName();
		$m = "[SubInventory] ";
		if($n == "CONSOLE"){
			$sender->sendMessage($this->isKorean() ? $m . "게임내에서만 사용가능합니다.": $m . "Please run this command in-game");
			return true;
		}
		$getInv = [];
		$inv = $sender->getInventory();
		if(!isset($this->si[$n])) $this->si[$n] = [];
		$getInv = [];
		foreach($inv->getContents() as $gI){
			if($gI->getID() !== 0 and $gI->getCount() > 0) $getInv[] = [$gI->getID(),$gI->getDamage(),$gI->getCount() ];
		}
		$setInv = [];
		foreach($this->si[$n] as $sI)
			$setInv[] = Item::get($sI[0], $sI[1], $sI[2]);
		$this->si[$n] = $getInv;
		$inv->setContents($setInv);
		$this->saveYml();
		$sender->sendMessage($this->isKorean() ? $m . "인벤토리가 교체되었습니다.": $m . "Inventory is change");
		return true;
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->subInventory = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "SubInventory.yml", Config::YAML);
		$this->si = $this->subInventory->getAll();
	}

	public function saveYml(){
		asort($this->si);
		$this->subInventory->setAll($this->si);
		$this->subInventory->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}