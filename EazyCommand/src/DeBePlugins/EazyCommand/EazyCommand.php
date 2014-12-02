<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\EazyCommand;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;

class EazyCommand extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$al = $this->alias->getAll();
		$rm = TextFormat::RED . "Usage: /EazyCommand ";
		$mm = "[EazyCommand] ";
		$ik = $this->isKorean();
 		switch(strtolower(array_shift($sub))){
			case "add":
			case "a":
			case "추가":
				if(!isset($sub[0]) || !isset($sub[1])){
					$r = $rm . ($ik ? "추가 <단축명> <명령어>": "Add(A) <Alias> <Command>");
				}else{
					$alias = strtolower(array_shift($sub));
					$command = str_replace([".@","_@","-@"],["@","@","@"], implode(" ", $sub));
					$al[$alias] = $command;
					$r = $mm . ($ik ? " 추가됨": " add") . "[$alias] => $command";
					;
				}
			break;
			case "del":
			case "d":
			case "삭제":
			case "제거":
				if(!isset($sub[0])){
					$r = $rm . "Del(D) <Alias>";
				}else{
					$a = strtolower($sub[0]);
					if(!isset($al[$a])){
						$r = "$mm [$a] " . ($ik ? " 목록에 존재하지 않습니다..\n   $rm 목록 ": " does not exist.\n   $rm List(L)");
					}else{
						$alias = $al[$a];
						unset($al[$a]);
						$r = $mm . ($ik ? " 제거됨": " del") . "[$a] =>$a";
					}
				}
			break;
			case "reset":
			case "r":
			case "리셋":
			case "초기화":
				$al = [];
				$r = $mm . ($ik ? " 리셋됨.": " Reset");
			break;
			case "list":
			case "l":
			case "목록":
			case "리스트":
				$page = 1;
				if(isset($sub[0]) && is_numeric($sub[0])) $page = round($sub[0]);
				$list = ceil(count($al) / 5);
				if($page >= $list) $page = $list;
				$r = $mm . ($ik ? "목록 (페이지": "List (Page") . " $page/$list) \n";
				$num = 0;
				foreach($al as $k => $v){
					$num++;
					if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$num] $k => $v \n";
				}
			break;
			case "debe":
			case "db":
			case "데베":
				$cnt = 0;
				foreach($this->getArrayBySite("DeBePlugins2.p-e.kr") as $cmds){
					$cmd = explode(",", $cmds);
					if(isset($cmd[1]) && isset($al[strtolower($cmd[0])]) && $al[strtolower($cmd[0])] == $cmd[1]){
						unset($al[strtolower($cmd[0])]);
						$cnt++;
						$sender->sendMessage($ik ? " 제거 [$cmd[0]] => $cmd[1]": " Del [$cmd[0]] => $cmd[1]");
					}
				}
				if($cmd > 0){
					$r = true;
					$sender->sendMessage("\n" . $mm . ($ik ? "자동으로 명령어 -> 제거완료 (갯수": "Auto DeBePlugin Command -> Del Complete (Count") . " : $cnt) ");
				}
				$cnt = 0;
				foreach($this->getArrayBySite("DeBePlugins.p-e.kr") as $cmds){
					$cmd = explode(",", $cmds);
					if(isset($cmd[1]) and !isset($al[strtolower($cmd[0])])){
						$al[strtolower($cmd[0])] = $cmd[1];
						$cnt++;
						$sender->sendMessage($ik ? " 추가 [$cmd[0]] => $cmd[1]": " Add [$cmd[0]] => $cmd[1]");
					}
				}
				if($cmd > 0) $r = "\n" . $mm . ($ik ? "자동으로 명령어 -> 추가완료 (갯수": "Auto DeBePlugin Command -> Add Complete (Count") . " : $cnt) ";
				if(!isset($r)) $r = "\n" . $mm . ($ik ? "최신 상태입니다. 업데이트가 필요없습니다.": "This is the latest of the state. The update does not need.");
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->al = $al;
		$this->saveYml();
		return true;
	}

	public function onServerCommand(ServerCommandEvent $event){
		$event->setCommand($this->alias($event->getCommand()));
		$m = $this->specialCommand($event);
		if($m !== false) $event->setCommand($m);
	}

	public function onRemoteServerCommand(RemoteServerCommandEvent $event){
		$event->setCommand($this->alias($event->getCommand()));
		$m = $this->specialCommand($event);
		if($m !== false) $event->setCommand($m);
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$this->setMessage($event);
		$m = $this->specialMessage($event);
		if($m !== false) $event->setMessage("/" . $m);
	}

	public function onPlayerChat(PlayerChatEvent $event){
		$m = $this->specialCommand($event);
		if($m !== false) $event->setMessage($m);
	}

	public function setMessage($event){
		$cmd = $event->getMessage();
		if(strpos($cmd, "/") !== 0) return;
		$cmd = substr($cmd, 1, strlen($cmd));
		$event->setMessage("/" . $this->alias($cmd));
	}

	public function alias($cmd){
 		$al = $this->al;
		$arr = explode(" ", $cmd);
		while((isset($al[strtolower($arr[0])]))){
			$arr[0] = $al[strtolower($arr[0])];
			$cmd = implode(" ", $arr);
			$arr = explode(" ", $cmd);
		}
		return $cmd;
	}

	public function specialMessage($event){
		if(strpos($event->getMessage(), "/") === 0) return $this->specialCommand($event);
		return false;
	}

	public function specialCommand($event){
		if($event->isCancelled()) return false;
		if($event instanceof PlayerCommandPreprocessEvent || $event instanceof PlayerChatEvent){
			$cmd = str_replace("/", "", $event->getMessage());
			$sender = $event->getPlayer();
		}else{
			$cmd = $event->getCommand();
			$sender = $event->getSender();
		}
		if(!$sender->hasPermission("debe.eazycommand.use")) return false;
		$arr = explode(" ", $cmd);
		$all = [];
		$ps = $this->getServer()->getOnlinePlayers();
		foreach($arr as $k => $v){
			if(strpos($v, "@") === 0){
				switch(strtolower(str_replace("@", "", $v))){
					case "username":
					case "user":
					case "u":
					case "player":
					case "p":
						$arr[$k] = $sender->getName();
					break;
					case "world":
					case "w":
						if($sender->getName() != "CONSOLE") $arr[$k] = $sender->getLevel()->getName();
					break;
					case "all":
					case "a":
						if($sender->isOp() || count($ps) > 0) $all[] = $k;
					break;
					case "random":
					case "r":
						$arr[$k] = count($ps) < 1 ? "": $ps[array_rand($ps)]->getName();
					break;
					case "server":
					case "s":
						$arr[$k] = $this->getServer()->getServerName();
					break;
					case "version":
					case "v":
						$arr[$k] = $this->getServer()->getApiVersion();
					break;
					case "debe":
					case "d":
						$arr[$k] = ["데베","DeBe","데베플러그인","DeBePlugins"][rand(0,3)];
					break;
				}
			}
		}
		if($all !== []){
			$event->setCancelled();
			foreach($ps as $p){
				foreach($all as $v)
					$arr[$v] = $p->getName();
				$cmd = implode(" ", $arr);
				$ep = false;
				if($event instanceof PlayerCommandPreprocessEvent){
					$ev = new PlayerCommandPreprocessEvent($sender, "/" . $cmd);
					$ep = true;
				}elseif($event instanceof PlayerChatEvent){
					$this->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($sender, $cmd));
					if(!$ev->isCancelled()) $this->getServer()->broadcastMessage(sprintf($ev->getFormat(), $ev->getPlayer()->getDisplayName(), $ev->getMessage()), $ev->getRecipients());
					return false;
				}elseif($event instanceof ServerCommandEvent){
					$ev = new ServerCommandEvent($sender, $cmd);
				}else{
					$ev = new RemoteServerCommandEvent($sender, $cmd);
				}
				$this->getServer()->getPluginManager()->callEvent($ev);
				if(!$ev->isCancelled()) $this->getServer()->dispatchCommand($sender, substr($ep ? $ev->getMessage(): $ev->getCommand(), 1));
			}
			return false;
		}else
			return implode(" ", $arr);
	}

	public function getArrayBySite($url){
		return explode("]", str_replace(["<!-- 단일 페이지 -->","<div>","</div>","<br>","\r","\n","["," ","_" ], ["","","","","","","",""," " ], file_get_contents("http://" . $url)));
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->alias = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "EazyCommand.yml", Config::YAML);
		$this->al = $this->alias->getAll();
	}

	public function saveYml(){
		ksort($this->al);
		$this->alias->setAll($this->al);
		$this->alias->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}