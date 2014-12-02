<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\MoveCommand;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class MoveCommand extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[3])) return false;
		$mm = "[Move] ";
		$ik = $this->isKorean();
		if(!$player = $this->getServer()->getPlayer(strtolower($sub[0]))) $r = $sub[0] . ($ik ? "는 잘못된 플레이어명입니다.": "is invalid player");
		elseif(!(is_numeric($x = $sub[1]) && is_numeric($y = $sub[2]) && is_numeric($z = $sub[3]))) $r = "<X> or <Y> or <Z> " . ($ik ? "중 하나가 숫자가 아닙니다.": "is not number");
		if(isset($r)) $sender->sendMessage($mm . $r);
		else $player->setMotion(new Vector3($x/2.5,$y/2.5,$z/2.5));
		return true;
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}