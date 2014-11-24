<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ClearEntities;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\entity\Arrow;
use pocketmine\entity\DroppedItem;
use pocketmine\entity\Living;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\scheduler\CallbackTask;

class ClearEntities extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[1])) return false;
		$mm = "[ClearEntities] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "item":
			case "i":
			case "아이템":
				$m = $ik ? "아이템": "Item";
				$set = true;
			break;
			case "arrow":
			case "a":
			case "화살":
				$m = $ik ? "화살": "Arrow";
				$set = true;
			break;
			case "monster":
			case "mob":
			case "m":
			case "몬스터":
				$m = $ik ? "몬스터": "Monster";
			break;
			default:
				$m = "";
			break;
		}
		$entities = [];
		$w = false;
		if(isset($sub[2])) $w = $this->getLevelByName($sub[2]);
		foreach($this->getServer()->getLevels() as $l){
			if($w || $w !== $l) continue;
			foreach($l->getEntities() as $e){
				switch($m){
					case "Item":
					case "아이템":
						$m = $ik ? "아이템": "Item";
						if($e instanceof DroppedItem) $entities[] = $e;
					break;
					case "Arrow":
					case "화살":
						$m = $ik ? "화살": "Arrow";
						if($e instanceof Arrow) $entities[] = $e;
					break;
					case "Monster":
					case "몬스터":
						if($e instanceof Living && !$e instanceof Player) $entities[] = $e;
					break;
					default:
						if(!$e instanceof Player) $entities[] = $e;
					break;
				}
			}
		}
		$c = count($entities);
		$s = !$w ? ($ik ? "이 서버": "This server") : "\"" . $w->getFolderName() . "\" " . ($ik ? "월드에": "world");
		switch(strtolower($sub[1])){
			case "view":
			case "v":
			case "보기":
				$r = $mm . ($c > 0 ? ($ik ? "$s 에는 $c 개의 $m 엔티티가 있습니다.": "$s has $c $m Entities. "): ($ik ? "$s 에는 $m 엔티티가 없습니다.": "$s don't has $m Entities."));
			break;
			case "clear":
			case "c":
			case "클리어":
			case "초기화":
				foreach($entities as $e)
					$e->close();
				$r = $mm . ($c > 0 ? ($ik ? "$s $m 엔티티를 제거했습니다. : $c": " Clear $s $m Entities : $c"): ($ik ? "$s 에는 $m 엔티티가 없습니다.": "$s don't has $m Entities."));
			break;
			case "set":
			case "s":
			case "설정":
				if(!isset($sub[2]) || !isset($set)){
					$r = "[ClearEntities] Usage: /ClearEntities " . ($ik ? "<설정> <아이템|화살> <초>": "<Set> <Item|Arrow> <Sec>");
				}else{
					$sub[2] = floor($sub[2]);
					if($m == "Item" || $m == "아이템"){
						if($sub[2] > 300) $sub[2] = 300;
						$this->clearEntities->set("Item",$sub[2]);
					}else{
						if($sub[2] > 60) $sub[2] = 60;
						$this->clearEntities->set("Arrow",$sub[2]);
					}
					$r = $mm . ($ik ? "$m 엔티티의 생존시간을 $sub[2] 초로 지정합니다.": "Set $m Entity's age to $sub[2] sec");
				}
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->saveYml();
		return true;
	}

	public function onEntitySpawn(EntitySpawnEvent $event){
		$e = $event->getEntity();
		if($e instanceof DroppedItem || $e instanceof Arrow) $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"kill" ], [$e ]), $this->clearEntities->get($e instanceof Arrow ? "Arrow": "Item")*20);
	}

	public function kill($e){
		$e->kill();
	}

	public function getLevelByName($name){
		foreach($this->getServer()->getLevels() as $l){
			if(strtolower($l->getFolderName()) == strtolower($name))
				return $l;
		}
		return false;
	}
	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->clearEntities = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "ClearEntities.yml", Config::YAML,["Item" => 60, "Arrow" => 10]);
	}

	public function saveYml(){
		$this->clearEntities->save();
		$this->loadYml();
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}