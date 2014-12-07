<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\JoinTime;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\TextFormat;

class JoinTime extends PluginBase implements Listener{

	public function onEnable(){
		$this->time = [];
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CallbackTask([$this,"upTime" ]), -1, 20);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		if(isset($sub[1])) $sub[1] = strtolower($sub[1]);
		$jt = $this->jt;
		$rm = TextFormat::RED . "Usage: /JoinTime ";
		$mm = "[JoinTime] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "view":
			case "v":
			case "보기":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "보기 <플레이어명>": "View(V) <PlayerName>");
				}elseif(!isset($jc[$sub[1]])){
					$r = $mm . $sub[1] . ($ik ? "는 잘못된 플레이어명입니다.": " is invalid player");
				}else{
					$r = $mm . $sub[1] . ($ik ? "' 접속시간 : " : "' Join Time : ") . $this->getDay($jt[$sub[1]]);
				}
			break;
			case "rank":
			case "r":
			case "랭크":
			case "랭킹":
			case "순위":
			case "목록":
				if(isset($sub[1]) && is_numeric($sub[1]) && $sub[1] > 1){
					$r = $this->getRank(round($sub[1]));
				}else{
					$r = $this->getRank(1);
				}
			break;
			case "clear":
			case "c":
			case "초기화":
			case "클리어":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "클리어 <플레이어명>": "Clear(C) <PlayerName>");
				}elseif(!isset($jt[$sub[1]])){
					$r = $mm . $sub[1] . ($ik ? "는 잘못된 플레이어명입니다.": " is invalid player");
				}else{
					$jt[$sub[1]] = 0;
					$r = $mm . $sub[1] . ($ik ? "' 접속시간를 초기화 합니다." : "' Join Time is Reset");
				}
			break;
			case "allclear":
			case "ac":
			case "전체초기화":
			case "전체클리어":
				$jt = [];
				$r = $mm . ($ik ? "모든 접속시간를 초기화합니다.": "Clear the All Join Time");
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->jt = $jt;
		$this->saveYml();
		return true;
	}

	public function upTime(){
		$jt = $this->jt;
		$t = $this->time;
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$n = strtolower($p->getName());
			if(!isset($this->jt[$n])) $this->jt[$n] = 0;
			$this->jt[$n]++;
		}
		$this->saveYml();
	}

	public function getRank($page = 1){
		$jt = $this->jt;
		arsort($jt);
		$list = ceil(count($jt) / 5);
		if($page >= $list) $page = $list;
		$r = ($this->isKorean ? "랭킹 (페이지 " : "Rank (Page ") . "$page/$list) \n";
		$num = 0;
		foreach($jt as $k => $v){
			$num++;
			if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$num] $k [" . $this->getDay($v) . "]\n";
		}
		return $r;
	}

	public function getDay($time = 0){
		$d = floor($time / 86400);
		$t = $time - ($d * 86400);
		$h = floor($time / 3600);
		$t = $t - ($h * 3600);
		$m = floor($t / 60);
		$s = $t - ($m * 60);
		return "$d:$h:$m:$s";
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->JoinTime = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "JoinTime.yml", Config::YAML, []);
		$this->jt = $this->JoinTime->getAll();
	}

	public function saveYml(){
		asort($this->jt);
		$this->JoinTime->setAll($this->jt);
		$this->JoinTime->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}