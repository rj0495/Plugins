<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\NickName;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\CallbackTask;

class NickName extends PluginBase{

	public function onEnable(){
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onTick" ]), 20);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!(isset($sub[1]) && $sub[0] && $sub[1])) return false;
		$mm = "[NickName] ";
		$ik = $this->isKorean();
		$n = strtolower(array_Shift($sub));
		$nn = implode(" ",$sub);
		if(!isset($this->nn[$n])){
			$r = $mm . $n . ($ik ? "는 잘못된 플레이어명입니다.": " is invalid player");
		}elseif(strlen($nn) > 20 || strlen($nn) < 3){
			$r = $mm . ($ik ? " 닉네임이 너무 길거나 짧습니다.." : "NickName is long or short") . " : $nn";
		}else{
			$r = $mm . $n . ($ik ? "' 닉네임 : " : "' NickName : ") . $nn;
			$this->nn[$n] = $nn;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->saveYml();
		return true;
	}

	public function onTick(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$c = false;
			$n = $p->getName();
			$sn = strtolower($n);
			if(!isset($this->nn[$sn])) $this->nn[$sn] = $n;
			$nn = $this->nn[$sn];
			if($p->getDisplayName() !== $nn){
				$p->setDisplayName($nn);
				$c = true;
			}
			if(strpos($p->getNameTag(), $nn) === false){
				$r = $p->setNameTag($nn);
				$c = true;
			}
			if($c) $p->sendMessage("[NickName] " . ($this->isKorean() ? " 당신의 닉네임이 $nn 으로 변경되엇습니다." : "Your nickname is change to $nn"));
		}
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->nickName = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "NickName.yml", Config::YAML, []);
		$this->nn = $this->nickName->getAll();
	}

	public function saveYml(){
		ksort($this->nn);
		$this->nickName->setAll($this->nn);
		$this->nickName->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}