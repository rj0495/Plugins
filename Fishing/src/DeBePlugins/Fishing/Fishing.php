<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\Fishing;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class Fishing extends PluginBase implements Listener{

	public function onEnable(){
		$this->fish = [];
		$this->cool = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$set = $this->set->getAll();
		$fish = $this->fish->getAll();
		$rm = TextFormat::RED . "Usage: /Fishing ";
		$mm = "[Fishing]";
		switch(strtolower($sub[0])){
			case "fishing":
			case "fs":
			case "on":
			case "off":
			case "낚시":
			case "온":
			case "오프":
				if($set["Fishing"] == "On"){
					$set["Fishing"] = "Off";
					$r = ($this->isKorean() ? "낚시를 끕니다.": "Fising is Off";
				}else{
					$set["Fishing"] = "On";
					$r = ($this->isKorean() ? "낚시를 켭니다.": "Fising is On";
				}
			break;
			case "item":
			case "i":
			case "아이템":
			case "템":
			case "낚시대":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "아이템 <아이템ID>": $rm . "Item(I) <ItemID>";
				}else{
					$i = Item::fromString($sub[1]);
					$i = $i->getID() . ":" . $i->getDamage();
					$set["Item"] = $i;
					$r = ($this->isKorean() ? "낚시 아이템을 [$i] 로 설정했습니다.": "Fishing Item is set [$i]";
				}
			break;
			case "useitem":
			case "ui":
			case "u":
			case "소모아이템":
			case "소모템":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "소모템 <아이템ID> <갯수>": $rm . "UseItem(U) <ItemID> <Amount>";
				}else{
					$i = Item::fromString($sub[1]);
					$i = $i->getID() . ":" . $i->getDamage();
					$cnt = 1;
					if(isset($sub[2]) && is_numeric($sub[2])) $cnt = $sub[2];
					$set["UseItem"] = ["ID" => $i,"Count" => $cnt ];
					$r = ($this->isKorean() ? "낚시 소모템을 [$i]  (Count: $cnt) 로 설정했습니다.": "Fishing UseItem is set [$i] (Count: $cnt)";
				}
			break;
			case "allitem":
			case "ai":
			case "a":
			case "모든아이템":
			case "모든템":
			case "전체아이템":
			case "전체템":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "모든템 <아이템ID> <갯수>": $rm . "AllItem(A) <ItemID> <Amount>";
				}else{
					$i = Item::fromString($sub[1]);
					$i = $i->getID() . ":" . $i->getDamage();
					$cnt = 1;
					if(isset($sub[2]) && is_numeric($sub[2])) $cnt = $sub[2];
					$set["Item"] = $i;
					$set["UseItem"] = ["ID" => $i,"Count" => $cnt ];
					$r = ($this->isKorean() ? "낚시 관련 모든템을 [$i]  (Count: $cnt) 로 설정했습니다.": "Fishing AllItem is set [$i] (Count: $cnt)";
				}
			break;
			case "delay":
			case "d":
			case "time":
			case "t":
			case "딜레이":
			case "시간":
			case "타임":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "딜레이 <시간>": $rm . "Delay(D) <Num>";
				}else{
					if($sub[1] < 0 || !is_numeric($sub[1])) $sub[1] = 0;
					if(isset($sub[2]) && $sub[2] > $sub[1] && is_numeric($sub[2]) !== false) $sub[1] = $sub[1] . "~" . $sub[2];
					$set["Delay"] = $sub[1];
					$r = ($this->isKorean() ? "낚시 딜레이를 [$sub[1]] 로 설정했습니다.": "Fishing Delay is set [$sub[1]]";
				}
			break;
			case "cool":
			case "cooltime":
			case "ct":
			case "쿨타임":
			case "쿨":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "쿨타임 <시간>": $rm . "CoolTime(CT) <Num>";
				}else{
					if(!isset($sub[1]) || $sub[1] < 0 || !is_numeric($sub[1])) $sub[1] = 0;
					$set["Cool"] = $sub[1];
					$r = ($this->isKorean() ? "낚시 쿨타임을 [$sub[1]] 로 설정했습니다.": "Fishing CoolTime is set [$sub[1]]";
				}
			break;
			case "count":
			case "c":
			case "횟수":
			case "갯수":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "횟수 <횟수>": $rm . "Count(C) <Num>";
				}else{
					if($sub[1] < 1 || !is_numeric($sub[1])) $sub[1] = 1;
					if(isset($sub[2]) && $sub[2] > $sub[1] && is_numeric($sub[2])) $sub[1] = $sub[1] . "~" . $sub[2];
					$set["Count"] = $sub[1];
					$r = ($this->isKorean() ? "물고기획득 횟수를 [$sub[1]] 로 설정했습니다.": "Get Fish count is set [$sub[1]]";
				}
			break;
			case "fishs":
			case "fish":
			case "f":
			case "물고기":
			case "피쉬":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "물고기 <추가|삭제|리셋|목록>": $rm . "Fishs(F) <Add|Del|Reset|List>";
				}else{
					switch(strtolower($sub[1])){
						case "add":
						case "a":
						case "추가":
							if(!isset($sub[2]) || !isset($sub[3])){
								$r = ($this->isKorean() ? $rm . "물고기 추가 <아이템ID> <확률> <갯수1> <갯수2>": $rm . "Fishs(F) Add(A) <ItemID> <Petsent> <Count1> <Count2>";
							}else{
								$i = Item::fromString($sub[2]);
								if($sub[3] < 1 || !is_numeric($sub[3])) $sub[3] = 1;
								if(!isset($sub[4]) < 0 || !is_numeric($sub[4])) $sub[4] = 0;
								if(isset($sub[5]) && $sub[5] > $sub[4] && is_numeric($sub[5])) $sub[4] = $sub[4] . "~" . $sub[5];
								$fish[] = ["Percent" => $sub[3],"ID" => $i->getID() . ":" . $i->getDamage(),"Count" => $sub[4] ];
								$r = ($this->isKorean() ? "물고기 추가됨 [" . $i->getID() . ":" . $i->getDamage() . " 갯수:$sub[4] 확률:$sub[3]]": "Fish add [" . $i->getID() . ":" . $i->getDamage() . " Count:$sub[4] Persent:$sub[3]]";
							}
						break;
						case "del":
						case "d":
						case "삭제":
						case "제거":
							if(!isset($sub[2])){
								$r = ($this->isKorean() ? $rm . "물고기 삭제 <번호>": $rm . "Fishs(F) Del(D) <FishNum>";
							}else{
								if($sub[2] < 0 || !is_numeric($sub[2])) $sub[2] = 0;
								if(!isset($fish[$sub[2] - 1])){
									$r = ($this->isKorean() ? "[$sub[2]] 는 존재하지않습니다. \n  " . $rm . "물고기 목록 ": "[$sub[2]] does not exist.\n  " . $rm . "Fish(F) List(L)";
								}else{
									$d = $fish[$sub[2] - 1];
									unset($fish[$sub[2] - 1]);
									$r = ($this->isKorean() ? "물고기 제거됨 [" . $d["ID"] . ":" . $i->getDamage() . " 갯수:" . $d["Count"] . " 확률:" . $d["Percent"] . "]": "Fish del [" . $d["ID"] . ":" . $i->getDamage() . " Count:" . $d["Count"] . " Persent:" . $d["Percent"] . "]";
								}
							}
						break;
						case "reset":
						case "r":
						case "리셋":
						case "초기화":
							$fish = [];
							$r = ($this->isKorean() ? "물고기 목록을 초기화합니다.": "Fish list is Reset";
						break;
						case "list":
						case "l":
						case "목록":
						case "리스트":
							$page = 1;
							if(isset($sub[2]) && is_numeric($sub[2])) $page = round($sub[2]);
							$list = ceil(count($fish) / 5);
							if($page >= $list) $page = $list;
							$r = ($this->isKorean() ? "목록 (페이지 $page/$list) \n": "List (Page $page/$list) \n";
							$num = 0;
							foreach($fish as $k){
								$num++;
								if($num + 5 > $page * 5 && $num <= $page * 5) $r .= ($this->isKorean() ? "  [$num] 아이디:" . $k["ID"] . " 갯수:" . $k["Count"] . " 확률:" . $k["Percent"] . " \n": "  [$num] ID:" . $k["ID"] . " Count:" . $k["Count"] . " Percent:" . $k["Percent"] . " \n";
							}
						break;
						default:
							return false;
						break;
					}
				}
			break;
			default:
				return false;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->set->setAll($set);
		$this->fish->setAll($fish);
		$this->saveYml();
		return true;
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if($this->set->get("Fishing") == "Off") return;
		$i = Item::fromString($this->set->get("Item"));
		$ii = $event->getItem();
		if($ii->getID() !== $i->getID() || $ii->getDamage() !== $i->getDamage()) return;
		$p = $event->getPlayer();
		$n = $p->getName();
		$m = ($this->isKorean() ? "[낚시]": "[Fishing] ";
		if(!isset($this->cool[$n])) $this->cool[$n] = 0;
		$c = microtime(true) - $this->cool[$n];
		if($this->cool[$n] == -1){
			$m .= ($this->isKorean() ? "이미 낚시를 시작했습니다. 기다려주세요.": "Already started fishing. Please wait.";
		}elseif($c < 0){
			$m .= ($this->isKorean() ? "쿨타임 :" . round($c * 100) / -100 . " 초": "Cool : " . round($c * 100) / -100 . " sec";
		}elseif($this->checkInven($p) !== true){
			$iv = $this->checkInven($p);
			$m .= ($this->isKorean() ? "당신은" . $iv[0] . "(." . $iv[1] . "개) 를 가지고있지않습니다. : " . $iv[2] . "개": "You Don't have " . $iv[0] . "($iv[1]) You have : " . $iv[2];
		}elseif($this->checkWater($event->getBlock()) !== true){
			$m .= ($this->isKorean() ? "이곳은 물이 아닙니다.": "Thare is not water";
		}else{
			$this->fishStart($p);
			unset($m);
		}
		if(isset($m)) $p->sendMessage($m);
		$event->setCancelled();
	}

	public function fishStart($p){
		$p->sendMessage($this->isKorean() ? "[낚시] 낚시를 시작했습니다. 기다려주세요.": "[Fishing] Started fishing. Please wait.");
		$time = $this->getTime();
		$this->cool[$p->getName()] = -1;
		for($for = 0; $for < $this->getCount(); $for++)
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"fishGive" ], [$p ]), $time);
	}

	public function fishGive($p){
		$this->cool[$p->getName()] = microtime(true) + $this->set->get("Cool");
		$i = $this->getFish();
		$p->getInventory()->addItem($i);
		$p->sendMessage($this->isKorean() ? "[낚시] $i (갯수:" . $i->getCount() . ") 를 얻었습니다.": "[Fishing] You get $i (count:" . $i->getCount() . ")");
	}

	public function getCount(){
		$c = explode("~", $this->set->get("Count"));
		if(isset($c[1])){
			$cnt = rand($c[0], $c[1]);
		}else{
			$cnt = $c[0];
		}
		return $cnt;
	}

	public function getTime(){
		$t = explode("~", $this->set->get("Delay"));
		if(isset($t[1])){
			$time = rand($t[0], $t[1]);
		}else{
			$time = $t[0];
		}
		return $time * 20;
	}

	public function getFish(){
		$d = $this->fishs;
		shuffle($d);
		$d = array_shift($d);
		$i = Item::fromString($d["ID"]);
		$c = explode("~", $d["Count"]);
		$i->setCount($c[0]);
		if(isset($c[1])) $i->setCount(rand($c[0], $c[1]));
		return $i;
	}

	public function checkInven($p){
		$ui = $this->set->get("UseItem");
		$i = Item::fromString($ui["ID"]);
		$c = $ui["Count"];
		$cnt = 0;
		$inv = $p->getInventory();
		foreach($inv->getContents() as $item){
			if($item->equals($i, $i->getDamage())) $cnt += $item->getCount();
			if($cnt >= $c) break;
		}
		if($cnt < $c){
			return [$i,$c,$cnt ];
		}else{
			$i->setCount($c);
			$this->removeItem($p, $i);
			return true;
		}
	}

	public function removeItem($p, $i){
		if($damage !== true) $damage = $i->getDamage();
		$ic = $i->getCount();
		$inv = $p->getInventory();
		foreach($inv->getContents() as $k => $ii){
			if($ii->equals($i, $damage)){
				if($ic - $ii->getCount() > 0){
					$inv->clear($k);
					$ic -= $ii->getCount();
				}else{
					$inv->setItem($k, Item::get($i->getID(), $i->getDamage(), $ii->getCount() - $ic));
					return true;
				}
			}
		}
		return false;
	}

	public function checkWater($b){
		$gx = $b->getX();
		$gy = $b->getY();
		$gz = $b->getZ();
		for($x = $gx - 3; $x <= $gx + 3; $x++){
			for($y = $gy - 3; $y <= $gy + 3; $y++){
				for($z = $gz - 3; $z <= $gz + 3; $z++){
					$id = $b->getLevel()->getBlock(new Vector3($x, $y, $z))->getID();
					if($id == 8 || $id == 9) return true;
				}
			}
		}
		return false;
	}

	public function loadYml(){
		$path = $this->getServer()->getDataPath() . "/plugins/! DeBePlugins/Fishing/";
		@mkdir($path);
		$this->set = new Config($path . "Setting.yml", Config::YAML, ["Item" => "280:0","UseItem" => ["ID" => "265:0","Count" => 1 ],"Fishing" => "On","Delay" => "3~5","Count" => "1~2" ]);
		if(is_file($path . "Fishs.yml") == true){
			$fish = [];
		}else{
			$fish = [["Percent" => 700,"ID" => "4:0","Count" => "1" ],["Percent" => 70,"ID" => "263","Count" => "1~3" ],["Percent" => 50,"ID" => "15:0","Count" => "1" ],["Percent" => 20,"ID" => "331:0","Count" => "1~7" ],["Percent" => 15,"ID" => "14:0","Count" => "1" ],["Percent" => 5,"ID" => "351:4","Count" => "1~7" ],["Percent" => 3,"ID" => "388:0","Count" => "1" ],["Percent" => 1,"ID" => "264:0","Count" => "1" ] ];
		}
		$this->fish = new Config($path . "Fishs.yml", Config::YAML, $fish);
		$this->fishs = [];
		foreach($this->fish->getAll() as $fish){
			for($for = 0; $for < $fish["Percent"]; $for++)
				$this->fishs[] = $fish;
		}
	}

	public function saveYml(){
		$this->set->save();
		$this->fish->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}