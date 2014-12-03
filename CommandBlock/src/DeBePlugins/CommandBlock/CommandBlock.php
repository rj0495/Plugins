<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CommandBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\scheduler\CallbackTask;

class CommandBlock extends PluginBase implements Listener{

	public function onEnable(){
		$this->touch = [];
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onTick" ]), 20);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$n = $sender->getName();
		if(!isset($sub[0])) return false;
		$cb = $this->cb;
		$t = $this->touch;
		$rm = "Usage: /CommandBlock ";
		$mm = "[CommandBlock] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "add":
			case "a":
			case "추가":
				$sc = true;
				if(isset($t[$n])){
					$r = $mm . ($ik ?  "커맨드 블럭 추가 해제": " CommandBlock Add Touch Disable");
					unset($t[$n]);
				}else{
					if(!isset($sub[1])){
						$r = $rm . ($ik ?  "추가 <명령어>": "Add(A) <Command>>");
					}else{
						array_shift($sub);
						$command = implode(" ", $sub);
						$r = $mm . ($ik ?  "대상 블럭을 터치해주세요. 명령어": "Touch the target block.  Command") . " : $command";
						$t[$n] = ["Type" => "Add","Command" => $command];
					}
				}
			break;
			case "del":
			case "d":
			case "삭제":
			case "제거":
				$sc = true;
				if(isset($t[$n])){
					$r = $mm . ($ik ?  "커맨드블럭 제거 해제": " CommandBlock Del Touch Disable");
					unset($t[$n]);
				}else{
					$r = $mm . ($ik ?  "대상 블럭을 터치해주세요. ": "Touch the block glass ");
					$t[$n] = ["Type" => "Del"];
				}
			break;
			case "reset":
			case "r":
			case "리셋":
			case "초기화":
				$cb = [];
				$r = $mm . ($ik ?  " 리셋됨.": " Reset");
			break;
			case "respawn":
			case "리스폰":
			case "rs":
				$r = $mm . ($ik ?  " 커맨드 리스폰됨.": " Spawn the Items");
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->cb = $cb;
		$this->touch = $t;
		$this->saveYml();
		return true;
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$b = $event->getBlock();
		$p = $event->getPlayer();
		$n = $p->getName();
		$t = $this->touch;
		$cb = $this->cb;
		$m = "[CommandBlock] ";
		$ik = $this->isKorean();
		if(isset($t[$n])){
			$pos = $this->getPos($b);
			$tc = $t[$n];
			switch($tc["Type"]){
				case "Add":
				 $m .= ($ik ?  "커맨드블럭이 생성되었습니다.": "CommandBlock Create") . " [$pos]";
					if(!isset($this->cb[$pos])) $this->cb[$pos] = [];
					$this->cb[$pos][] = $tc["Command"];
 					unset($t[$n]);
				break;
				case "Del":
					if(!isset($cb[$pos])){
						$m .= $ik ?  "이곳에는 커맨드 블럭이 없습니다.": "CommandBlock is not exist here";
					}else{
						$m .= ($ik ?  "커맨드블럭이 제거되었습니다.": "CommandBlock is Delete ") . "[$pos]";
						unset($this->cb[$pos]);
						unset($t[$n]);
					}
				break;
			}
			$this->touch = $t;
			if(isset($m)) $p->sendMessage($m);
			$this->saveYml();
			$event->setCancelled();
		}else{
			$this->onBlockEvent($event);
 		}
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$this->onBlockEvent($event);
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$this->onBlockEvent($event);
	}

	public function onBlockEvent($event){
		$pos = $this->getPos($event->getBlock());
		if(isset($this->cb[$pos])){
			if(!$event->getPlayer()->hasPermission("debe.commandblock.block")) $event->setCancelled();
			if($event->getPlayer()->hasPermission("debe.commandblock.touch")) $this->runCommand($event->getPlayer(),$pos);
		}
	}

	public function onTick(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if(!$p->hasPermission("debe.commandblock.push")) continue;
				$pos = $this->getPos($p->add(0,-1,0),$p->getLevel()->getName());
	 			if(isset($this->cb[$pos])){
				foreach($this->cb[$pos] as $cmd){
					if(strpos($cmd, "%b") !== false){
						$this->runCommand($p,$pos,true);
						break;
					}
				}
			}
		}
	}

	public function runCommand($p, $pos, $isBlock = false){
		if(!isset($this->cb[$pos])) return false;
		$cb = $this->cb[$pos];
		foreach($cb as $str){
			$arr = explode(" ", $str);
			$chat = false;
			$console = false;
			$op = false;
			$block = false;
			foreach($arr as $k => $v){
				if(strpos($v, "%") === 0){
					$kk = $k;
					switch(strtolower(substr($v,1))){
						case "username":
						case "user":
						case "u":
						case "player":
						case "p":
							$arr[$k] = $p->getName();
						break;
						case "world":
						case "w":
							$arr[$k] = $p->getLevel()->getFolderName();					
						break;
						case "mainworld":
						case "mw":
							$arr[$k] = $this->getDefaultLevel()->getFolderName();					
						break;
						case "random":
						case "r":
							$ps = $this->getServer()->getOnlinePlayers();
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
						case "op":
							unset($arr[$k]);					
							$op = true;
						break;
						case "chat":
						case "c":
							unset($arr[$k]);					
							$chat = true;
						break;
						case "console":
						case "cs":
							unset($arr[$k]);					
							$console = true;
						break;
						case "block":
						case "b":
							unset($arr[$k]);					
							$block = true;
						break;
 					}
 					if(isset($arr[$kk])){
 						$ak = strtolower($arr[$k]);
						if(strpos($ak, "%dice") === 0 || strpos($ak, "%d") === 0){
							$e = explode(":", $ak);
							if(isset($e[1])){
								$ee = explode(",", $e[1]);
								if(isset($ee[1])) $arr[$k] = rand($ee[0], $ee[1]);
							}
						}
					}
				}
			}
			if($isBlock && !$block || !$isBlock && $block) continue;
			$cmd = implode(" ", $arr);
			if($chat){
				$p->sendMessage($cmd);
			}else{
				$ev = $console ? new ServerCommandEvent($sender, $cmd) : new PlayerCommandPreprocessEvent($p, "/" . $cmd);
				$this->getServer()->getPluginManager()->callEvent($ev);
				if(!$ev->isCancelled()){
					$op = $op && !$p->isOp() && !$console;
					if($op) $p->setOp(true);
					if($ev instanceof ServerCommandEvent) $this->getServer()->dispatchCommand(new ConsoleCommandSender(), substr($ev->getCommand(), 1));
					else $this->getServer()->dispatchCommand($p, substr($ev->getMessage(), 1));
					if($op) $p->setOp(false);
				}
			}
		}
	}

	public function getPos($b, $level = false){
		return floor($b->getX()) . ":" . floor($b->getY()) . ":" . floor($b->getZ()) . ":" . (!$level ? $b->getLevel()->getName() : $level);
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->commandblock = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "CommandBlock.yml", Config::YAML);
		$this->cb = $this->commandblock->getAll();
	}

	public function saveYml(){
		ksort($this->cb);
		$this->commandblock->setAll($this->cb);
		$this->commandblock->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}