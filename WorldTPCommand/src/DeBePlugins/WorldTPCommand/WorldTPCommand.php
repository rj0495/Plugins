<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldTPCommand;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\level\Position;

class WorldTPCommand extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[4])) return false;
		$mm = "[WorldTP] ";
		$ik = $this->isKorean();
		if(is_numeric($x = $sub[0]) && is_numeric($y = $sub[1]) && is_numeric($z = $sub[2]) && $sender instanceof Player){
			$player = $sender;
			if(!$world = $this->getServer()->getLevelByName($sub[3])) unset($world);
		}
		if(!isset($player) &&  !$player = $this->getServer()->getPlayer(strtolower($sub[0]))) $r = $sub[0] . ($ik ? "는 잘못된 플레이어명입니다.": "is invalid player");
		elseif(!isset($x) && !(is_numeric($x = $sub[1]) && $is_numeric($y = $sub[2]) && is_numeric($z = $sub[3]))) $r = "<X> or <Y> or <Z> " . ($ik ? "중 하나가 숫자가 아닙니다.": "is not number");
		elseif(!isset($world) && $!$world = $this->getServer()->getLevelByName($sub[4)) $r = $sub[4] . ($ik ? "는 잘못된 월드명입니다.": "is invalid world");
		if(isset($r)) $sender->sendMessage($mm . $r);
		else $player->teleport(new Position($x,$y,$z,$world));
		return true;
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}