<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\DamageBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\CallbackTask;
use pocketmine\math\Vector3;
use pocketmine\math\Math;
use pocketmine\item\Item;

class DamageBlock extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"DamageBlock" ]), 20);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$db = $this->damageBlock->getAll();
		$rm = TextFormat::RED . "Usage: /DamageBlock ";
		$mm = "[DamageBlock] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "add":
			case "a":
			case "추가":
				if(!isset($sub[1]) || !isset($sub[2])){
					$r = $rm . ($ik ? "추가 <블럭ID> <데미지1> <데미지2>": "Add(A) <BlockID> <Damage1> <Damage2>");
				}else{
					$i = Item::fromString($sub[1]);
					if($i->getID() == 0 && $sub[1] !== 0){
						$r = $sub[1] . " " . ($ik ? "는 잘못된 블럭ID입니다..": "is invalid BlockID");
					}else{
						$id = $i->getID() . ":" . $i->getDamage();
						if(!is_numeric($sub[2])) $sub[2] = 0;
						$sub[2] = round($sub[2]);
						if(isset($sub[3]) && $sub[3] > $sub[2] && is_numeric($sub[3])) $sub[2] = $sub[2] . "~" . round($sub[3]);
						$db[$id] = $sub[2];
						$r = $mm . ($ik ? "추가됨": "Add") . " [$id] => $sub[2]";
					}
				}
			break;
			case "del":
			case "d":
			case "제거":
			case "삭제":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "제거 <블럭ID>": "Del(D) <BlockID>");
				}else{
					$i = Item::fromString($sub[1]);
					if($i->getID() == 0 && $sub[1] !== 0){
						$r = $sub[1] . " " . ($ik ? "는 잘못된 블럭ID입니다..": "is invalid BlockID");
					}else{
						$id = $i->getID() . ":" . $i->getDamage();
						if(!isset($db[$id])){
							$r = "$mm [$id] " . ($ik ? " 목록에 존재하지 않습니다..\n   $rm 목록 ": " does not exist.\n   $rm List(L)");
						}else{
							foreach($db as $k => $v){
								if($k == $id) unset($db[$k]);
							}
							$r = $mm . ($ik ? "제거됨": "Del") . "[$id]";
						}
					}
				}
			break;
			case "reset":
			case "r":
			case "리셋":
			case "초기화":
				$db = [];
				$r = $mm . ($ik ? " 리셋됨.": " Reset");
			break;
			case "list":
			case "l":
			case "목록":
			case "리스트":
				$page = 1;
				if(isset($sub[0]) && is_numeric($sub[0])) $page = round($sub[0]);
				$list = ceil(count($db) / 5);
				if($page >= $list) $page = $list;
				$r = $mm . ($ik ? "목록 (페이지": "List (Page") . " $page/$list) \n";
				$num = 0;
				foreach($db as $k => $v){
					$num++;
					if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$num] $k : $v \n";
				}
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->db = $db;
		$this->saveYml();
		return true;
	}

	public function DamageBlock(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($p->hasPermission("debe.damageblock.inv")) continue;
			$bb = $p->getBoundingBox();
			$minX = Math::floorFloat($bb->minX - 0.001);
			$minY = Math::floorFloat($bb->minY - 0.001);
			$minZ = Math::floorFloat($bb->minZ - 0.001);
			$maxX = Math::floorFloat($bb->maxX + 0.001);
			$maxY = Math::floorFloat($bb->maxY + 0.001);
			$maxZ = Math::floorFloat($bb->maxZ + 0.001);
			$block = [];
			$damage = 0;
			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$getDamage = $this->getDamage($p->getLevel()->getBlock(new Vector3($x, $y, $z)));
						if(!in_array($getDamage[1], $block)){
							$damage += $getDamage[0];
							$block[] = $getDamage[1];
						}
					}
				}
			}
			if($damage !== 0) $p->attack($damage);
		}
	}

	public function getDamage($b){
		$id = $b->getID() . ":" . $b->getDamage();
		if(!isset($this->db[$id])) return false;
		$d = explode("~", $this->db[$id]);
		if(isset($d[1])) $damage = rand($d[0], $d[1]);
		else $damage = $d[0];
		return [$damage,$id];
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->damageBlock = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "DamageBlock.yml", Config::YAML, []);
		$this->db = $this->damageBlock->getAll();
	}

	public function saveYml(){
		ksort($this->db);
		$this->damageBlock->setAll($this->db);
		$this->damageBlock->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}