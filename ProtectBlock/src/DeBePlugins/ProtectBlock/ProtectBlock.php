<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ProtectBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class ProtectBlock extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$pb = $this->protectBlock->getAll();
		$rm = TextFormat::RED . "Usage: /ProtectBlock ";
		$mm = "[ProteckBlock] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "add":
			case "a":
			case "추가":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "추가 <블럭ID>": "Add(A) <BlockID>");
				}else{
					$i = Item::fromString($sub[1]);
					if($i->getID() == 0){
						$r = $mm . $sub[1] . " " . ($ik ? "는 잘못된 블럭ID입니다..": "is invalid BlockID");
					}else{
						$id = $i->getID() . ":" . $i->getDamage();
						if(!in_array($id, $pb)) $pb[] = $id;
						$r = $mm . ($ik ? " 추가됨 ": "Add") . " [$id]";
					}
				}
			break;
			case "del":
			case "d":
			case "삭제":
			case "제거":
				if(!isset($sub[1])){
					$r = $rm . ($ik ? "제거 <블럭ID>": "Del(D) <BlockID>");
				}else{
					$i = Item::fromString($sub[1]);
					if($i->getID() == 0){
						$r = $mm . $sub[1] . " " . ($ik ? "는 잘못된 블럭ID입니다..": "is invalid BlockID");
					}else{
						$id = $i->getID() . ":" . $i->getDamage();
						if(!in_array($id, $pb)){
							$r = " [$id] " . ($ik ? "목록에 존재하지 않습니다.\n $rm 목록 ": "does not exist.\n $rm List(L)");
						}else{
							foreach($pb as $k => $v){
								if($v == $id) unset($pb[$k]);
							}
							$r = $mm . ($ik ? " 제거됨 ": "Del") . " [$id]";
							;
						}
					}
				}
			break;
			case "reset":
			case "r":
			case "리셋":
			case "초기화":
				$pb = [];
				$r = $mm . ($ik ? " 리셋됨.": " Reset");
			break;
			case "list":
			case "l":
			case "목록":
			case "리스트":
				$page = 1;
				if(isset($sub[1]) && is_numeric($sub[1]) && $sub[1] >= 1) $page = round($sub[1]);
				$list = ceil(count($pb) / 5);
				if($page >= $list) $page = $list;
				$r = $mm . ($ik ? "목록 (페이지": "List (Page") . " $page/$list) \n";
				$num = 0;
				foreach($pb as $v){
					$num++;
					if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$num] $v\n";
				}
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->pb = $pb;
		$this->saveYml();
		return true;
	}

	public function onBlockBreak(BlockBreakEvent $event){
		if(!$event->isCancelled()) $this->protectBlock($event);
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		if(!$event->isCancelled()) $this->protectBlock($event);
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if(!$event->isCancelled()) $this->protectBlock($event);
	}

	public function protectBlock($event){
		$b = $event->getBlock();
		if(!$event->getPlayer()->hasPermission("debe.protectblock.block") && in_array($b->getID() . ":" . $b->getDamage(), $this->pb)) $event->setCancelled();
		}
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->protectBlock = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "ProtectBlock.yml", Config::YAML, []);
		$this->pb = $this->protectBlock->getAll();
	}

	public function saveYml(){
		sort($this->pb);
		$this->protectBlock->setAll($this->pb);
		$this->protectBlock->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}