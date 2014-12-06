<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ItemCase;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\MoveEntityPacket;

class ItemCase extends PluginBase implements Listener{

	public function onEnable(){
		$this->touch = [];
		$this->place = [];
		$this->item = [];
		$this->level = [];
		$this->time = false;
		$this->eid = 99999;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$n = $sender->getName();
		if(!isset($sub[0])) return false;
		$ic = $this->ic;
		$t = $this->touch;
		$rm = "Usage: /ItemCase ";
		$mm = "[ItemCase] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "add":
			case "a":
			case "추가":
				$sc = true;
				if(isset($t[$n])){
					$r = $mm . ($ik ?  "아이템케이스 추가 해제": " ItemCase Add Touch Disable");
					unset($t[$n]);
				}else{
					if(!isset($sub[1])){
						$r = $rm . ($ik ?  "추가 <아이템ID> (크기": "Add(A) <ItemID> (Size") . "1~3)";
					}else{
						$i = Item::fromString($sub[1]);
						if($i->getID() == 0){
							$r = $sub[1] . " " . ($ik ? "는 잘못된 블럭ID입니다..": "is invalid BlockID");
						}else{
							$id = $i->getID() . ":" . $i->getDamage();
							$size = 1;
							if(isset($sub[2]) && is_numeric($sub[2]) && $sub[2] >= 1){
								$size = round($sub[2]);
								if($size > 5) $size = 5;
							}
							$r = $mm . ($ik ?  "대상 블럭을 터치해주세요. \n 아이디 : $id 크기 : $size": "Touch the target block \n ID : $id Size : $size");
							$t[$n] = ["Type" => "Add","Item" => $id,"Size" => $size ];
						}
					}
				}
			break;
			case "del":
			case "d":
			case "삭제":
			case "제거":
				$sc = true;
				if(isset($t[$n])){
					$r = $mm . ($ik ?  "아이템케이스 제거 해제": " ItemCase Del Touch Disable");
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
				$ic = [];
				$r = $mm . ($ik ?  " 리셋됨.": " Reset");
			break;
			case "respawn":
			case "리스폰":
			case "rs":
				$r = $mm . ($ik ?  " 아이템 리스폰됨.": " Spawn the Items");
			break;
			case "glass":
			case "g":
			case "case":
			case "c":
			case "유리":
			case "케이스":
				foreach($this->ic as $k => $list){
					foreach($list as $item => $v){
						$pos = explode(":", $k);
						$l = $this->getServer()->getLevelByName($pos[3]);
						if($l != false) $l->setBlock(new Vector3(...$pos),Block::get(20));
					}
				}
				$r = $mm . ($ik ?  " 유리 설치됨.": " Place the Glass");
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->ic = $ic;
		$this->touch = $t;
		$this->saveYml();
		if(!isset($sc)) $this->spawnCase();
		return true;
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$b = $event->getBlock();
		if($b->getID() !== 20) $b = $b->getSide($event->getFace());
		$p = $event->getPlayer();
		$n = $p->getName();
		$t = $this->touch;
		$ic = $this->ic;
		$m = "[ItemCase] ";
		$ik = $this->isKorean();
		if(isset($t[$n])){
			$pos = $this->getPos($b);
			$tc = $t[$n];
			switch($tc["Type"]){
				case "Add":
					if(!$this->addCase($pos, $tc["Item"], $tc["Size"])) $m .= $ik ?  "이미 3개가 존재합니다.": "Already 3 ItemCase Here";
					else $m .= ($ik ?  "아이템케이스가 생성되었습니다.": "ItemCase Create") . " [$pos]";
					unset($t[$n]);
				break;
				case "Del":
					if(!isset($ic[$pos])){
						$m .= $ik ?  "이곳에는 아이템 케이스가 없습니다.": "ItemCase is not exist here";
					}else{
						$m .= ($ik ?  "아이템케이스가 제거되었습니다.": "ItemCase is Delete ") . "[$pos]";
						$this->delCase($pos);
						unset($t[$n]);
					}
				break;
			}
			$this->touch = $t;
			$this->spawnCase();
			if(isset($m)) $p->sendMessage($m);
			$event->setCancelled();
			if($event->getItem()->isPlaceable()){
				$this->place[$p->getName()] = true;
			}
		}else{
			$this->onBlockEvent($event,true);
 		}
	}

	public function onPlayerRespawn(PlayerRespawnEvent $event){
		$this->spawnCase();
	}

	public function onPlayerMove(PlayerMoveEvent $event){
		$p = $event->getPlayer();
		$n = $p->getName();
		$l = $p->getLevel()->getFolderName();
		if(!isset($this->level[$n])) $this->level[$n] = $l;
		elseif($this->level[$n] !== $l) $this->spawnCase(true);
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$this->onBlockEvent($event);
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$this->onBlockEvent($event);
	}

	public function onBlockEvent($event,$isTouch = false){
		$p = $event->getPlayer();
		if(isset($this->place[$p->getName()])){
			$event->setCancelled();
			unset($this->place[$p->getName()]);
		}
		if(isset($this->ic[$this->getPos($event->getBlock())])){
			if($isTouch && $event->getItem()->isPlaceable()){
				$this->place[$p->getName()] = true;
			}
			if(!$p->hasPermission("debe.itemcase.block")) $event->setCancelled();
			if($p->hasPermission("debe.itemcase.spawn")) $this->spawnCase(true);
		}
	} 

	public function spawnCase($time = false){
		if(!$this->time) $this->time = microtime(true);
		if($time && time(true) - $this->time < 5) return; 
		$this->time = time(true);
 		$this->despawnCase();
		foreach($this->ic as $k => $list){
			$count = 0;
			foreach($list as $item => $v){
				if($this->eid > 2100000000) $this->eid = 9999;
				$i = Item::fromString($v[0]);
				if($v[1] == 2) $size = 5;
				elseif($v[1] == 3) $size = 10;
				elseif($v[1] == 4) $size = 30;
				elseif($v[1] == 5) $size = 100;
				else $size = 1;
				$i->setCount($size);
				$pk = new AddItemEntityPacket();
				$pk->eid = $this->eid;
				$pk->item = $i;
				$pos = explode(":", $k);
				$pk->x = $pos[0] + 0.5;
				$pk->y = $pos[1];
				$pk->z = $pos[2] + 0.5;
				$pk->yaw = 0;
				$pk->pitch = 0;
				$pk->roll = 0;
				$this->dataPacket($pk, $k);
				$pk = new MoveEntityPacket();
				if($count % 2 == 1) $dis = $count * 0.15;
				else $dis = $count * -0.15;
				$count++;
				$pk->entities = [[$this->eid,$pos[0] + 0.5 + $dis,$pos[1] + 0.25,$pos[2] + 0.5 + $dis,0,0 ] ];
				$this->dataPacket($pk, $k);
				$this->item[] = $this->eid;
				$this->eid++;
				$this->dataPacket($pk, $k);
			}
		}
	}

	public function despawnCase(){
		foreach($this->item as $v){
			$pk = new RemoveEntityPacket();
			$pk->eid = $v;
			$this->dataPacket($pk);
		}
		$this->item = [];
	}

	public function addCase($pos, $id, $size){
		if(!isset($this->ic[$pos])) $this->ic[$pos] = [];
		if(count($this->ic[$pos]) == 3) return false;
		$this->ic[$pos][count($this->ic[$pos])] = [$id,round($size)];
		$this->saveYml();
		return true;
	}

	public function delCase($pos){
		$this->spawnCase();
		unset($this->ic[$pos]);
		$this->saveYml();
	}

	public function dataPacket($pk, $pos = ""){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($pk instanceof RemoveEntityPacket || $p->getLevel()->getName() == explode(":", $pos)[3]) $p->directDataPacket($pk);
		}
	}

	public function getPos($b){
		return $b->getX() . ":" . $b->getY() . ":" . $b->getZ() . ":" . $b->getLevel()->getName();
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->itemcase = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "ItemCase.yml", Config::YAML);
		$this->ic = $this->itemcase->getAll();
	}

	public function saveYml(){
		ksort($this->ic);
		$this->itemcase->setAll($this->ic);
		$this->itemcase->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}