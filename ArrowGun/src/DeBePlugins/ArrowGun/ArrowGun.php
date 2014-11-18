<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ArrowGun;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;
use pocketmine\entity\Arrow;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\item\Item;
use pocketmine\utils\Config;

class ArrowGun extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$mm = "[ArrowGun] ";
		if($sender->getName() == "CONSOLE"){
			$sender->sendMessage($mm . ($this->isKorean() ? "게임내에서만 사용가능합니다.": "Please run this command in-game"));
			return true;
		}
		$nbt = new Compound("", ["Pos" => new Enum("Pos", [new Double("", $sender->getx()),new Double("", $sender->gety() + $sender->getEyeHeight()),new Double("", $sender->getz()) ]),"Motion" => new Enum("Motion", [new Double("", -sin($sender->getyaw() / 180 * M_PI) * cos($sender->getPitch() / 180 * M_PI)),new Double("", -sin($sender->getPitch() / 180 * M_PI)),new Double("", cos($sender->getyaw() / 180 * M_PI) * cos($sender->getPitch() / 180 * M_PI)) ]),"Rotation" => new Enum("Rotation", [new Float("", $sender->getyaw()),new Float("", $sender->getPitch()) ]) ]);
		$arrow = new Arrow($sender->chunk, $nbt, $sender);
		$ev = new EntityShootBowEvent($sender, Item::get(264, 0, 0), $arrow, 1.5);
		$this->getServer(0)->getPluginManager()->callEvent($ev);
		if($ev->isCancelled()) $arrow->kill();
		else $arrow->spawnToAll();
		return true;
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}