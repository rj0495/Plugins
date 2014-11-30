<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CountdownServerStop;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\Config;

class CountdownServerStop extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$rm = "Usage: /CountdownServerStop";
		$mm = "[CountdownServerStop] ";
		if(!isset($sub[0]) or !is_numeric($sub[0])) $this->countdown(10);
		elseif($sub[0] < 1) $this->getServer()->shutdown();
		else $this->countdown(round($sub[0]));
		return true;
	}

	public function countdown($t, $tt = false){
		if(!$tt) $tt = 0;
		if($t - $tt < 1){
			$this->getServer()->shutdown();
			return;
		}else{
			$tt++;
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"broadCast" ], [$this->isKorean() ? "[CountDownServerStop] 서버가 " . $t - $tt . " 초 후에 종료됩니다.": "[CountDownServerStop] Server is stop to $time" ]), 20);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"countdown" ], [$t,$tt ]), 20);
		}
	}

	public function broadCast($m){
		$this->getServer()->broadcastMessage($m);
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}