<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CommandLog;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class CommandLog extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		if(isset($sub[1])) $sub[1] = strtolower($sub[1]);
		$cl = $this->cl;
		$rm = TextFormat::RED . "Usage: /CommandLog ";
		$mm = "[CommandLog] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "view":
			case "v":
			case "보기":
			case "뷰":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "보기 <플레이어명> <페이지>": "View(V) <PlayerName> <Page>");
				}elseif(!isset($cl[$sub[1]])){
					$sender->sendMessage($mm . $sub[1] . ($ik ? "는 잘못된 플레이어명입니다.": " is invalid player"));
				}else{
					if(!isset($sub[2]) || !is_numeric($sub[2] || $sub[2] < 1)) $sub[2] = 1;
					$page = round($sub[2]);
					if(isset($sub[0]) && is_numeric($sub[0])) $page = round($sub[0]);
					$list = ceil(count($cl[$sub[1]]) / 5);
					if($page >= $list) $page = $list;
					$r = $mm . $sub[1] . ($ik ? "' 로그 (페이지": "' Command Log (Page") . " $page/$list) \n";
					$num = 0;
					foreach($cl[$sub[1]] as $k => $v){
						$num++;
						if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$k] $v\n";
					}
				}
			break;
			case "clear":
			case "c":
			case "초기화":
			case "클리어":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "초기화 <플레이어명>": "Clear(C) <PlayerName>");
				}elseif(!isset($cl[$sub[1]])){
					$sender->sendMessage($mm . $sub[1] . ($ik ? "는 잘못된 플레이어명입니다.": " is invalid player"));
				}else{
					$cl[$sub[1]] = [];
					$r = $mm . ($ik ? $sub[1] . "님의 명령어 로그를 제거합니다.": "Clear the $sub[1]'s Command log");
				}
			break;
			case "allclear":
			case "ac":
			case "전체초기화":
			case "전체클리어":
				$cl = [];
				$r = $ik ? "모든 명령어 로그를 제거합니다.'": "Clear the All Command log";
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->cl = $cl;
		$this->saveYml();
		return true;
	}

	public function onServerCommand(ServerCommandEvent $event){
		$this->log($event->getCommand(), $event->getSender());
	}

	public function onRemoteServerCommand(RemoteServerCommandEvent $event){
		$this->log($event->getCommand(), $event->getSender());
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$cmd = $event->getMessage();
		if(strpos($cmd, "/") !== 0) return false;
		$this->log(substr($cmd, 1), $event->getPlayer());
	}

	public function log($cmd, $sender){
		$name = strtolower($sender->getName());
		if($name == "console" && $cmd == "list" || $name == "console" && $cmd == "stop") return false;
		$this->cl[$name][date("Y:m:d|H:i:s", time())] = $cmd;
		$this->saveYml();
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->commandlog = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "CommandLog.yml", Config::YAML, []);
		$this->cl = $this->commandlog->getAll();
	}

	public function saveYml(){
		ksort($this->cl);
		$this->commandlog->setAll($this->cl);
		$this->commandlog->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}