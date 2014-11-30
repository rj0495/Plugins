<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\TeleportView;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\utils\Config;

class TeleportView extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$mm = "[TeleportView] ";
		if($sender->getName() == "CONSOLE"){
			$sender->sendMessage($mm . ($this->isKorean() ? "게임내에서만 사용가능합니다.": "Please run this command in-game"));
			return true;
		}
		$yaw = $sender->getYaw();
		$ptch = $sender->getPitch();
		$yawS = -sin($yaw / 180 * M_PI);
		$yawC = cos($yaw / 180 * M_PI);
		$ptchS = -sin($ptch / 180 * M_PI);
		$ptchC = cos($ptch / 180 * M_PI);
		$x = $sender->getX();
		$y = $sender->getY() + $sender->getEyeHeight();
		$z = $sender->getZ();
		$l = $sender->getLevel();
		for($f = 0; $f < 50; ++$f){
			$x += $yawS * $ptchC;
			$y += $ptchS;
			$z += $yawC * $ptchC;
			$b = $l->getBlock(new Position($x, $y, $z, $l));
			if($b->isSolid){
				$sender->teleport(new Position($x - $yawS * $ptchC, $y - $ptchS + 0.1, $z - $yawC * $ptchC, $l));
				$f = true;
			}
		}
		if(!isset($f)) $sender->sendMessage($mm . ($this->isKorean() ? "타겟 블럭이 너무 멉니다.": "TargetBlock is too far"));
		return true;
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}