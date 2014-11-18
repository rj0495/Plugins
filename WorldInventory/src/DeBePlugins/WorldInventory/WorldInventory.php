<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldInventory;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
class WorldInventory extends PluginBase implements Listener{

	public function onEnable(){
		$this->loadYml();
		$this->saveYml();
		$this->gmc = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

 	public function onPlayerMove(PlayerMoveEvent $event){
		$p = $event->getPlayer();
		if($p->isCreative()) return;
		$n = strtolower($p->getName());
 		$wn = strtolower($event->getTo()->getLevel()->getFolderName());
		$this->createInv($p,$wn);
		$wi = $this->wi[$n];
		$wiw = $wi["Worlds"];
 		$wil = $wi["LastWorld"];
		$inv = $p->getInventory();
 		if(isset($this->gmc[$n])){
 			foreach($this->gmc[$n] as $k => $i){
				$this->gmc[$n][$k] = Item::get(...(explode(":",$i)));
			}
			$inv->setContents($this->gmc[$n]);
			unset($this->gmc[$n]);
 			$change = true;
 		}
		if($wil !== $wn){
			$wiw[$wil] = [];
			if(!isset($wiw[$wn])) $wiw[$wn] = [];
			foreach($inv->getContents() as $i){
				if($i->getID() !== 0 and $i->getCount() > 0) $wiw[$wil][] = $i->getID().":".$i->getDamage().":".$i->getCount();
			}
			foreach($wiw[$wn] as $k => $i){
				$wiw[$wn][$k] = Item::get(...(explode(":",$i)));
			}
			$inv->setContents($wiw[$wn]);
			$wiw[$wn] = [];
 			$this->wi[$n] = ["LastWorld" => $wn, "Worlds" => $wiw];
			$this->saveYml();
			$p->sendMessage("[WorldInventory] " . ($this->isKorean() ? "인벤토리가 교체되었습니다.": "Inventory is change") . " : WorldChange");
		}
	}

 	public function onPlayerGameModeChange(PlayerGameModeChangeEvent $event){
 		if($event->isCancelled()) return;
		$p = $event->getPlayer();
		$n = strtolower($p->getName());
		$g = $event->getNewGamemode();
 		$wn = strtolower($p->getLevel()->getFolderName());
		$this->createInv($p,$wn);
		$wiw = $this->wi[$n]["Worlds"][$wn];
		$g = $event->getNewGamemode();
		if($g == 1){
			$inv = $p->getInventory();
			foreach($inv->getContents() as $i){
				if($i->getID() !== 0 and $i->getCount() > 0) $wiw[] = $i->getID().":".$i->getDamage().":".$i->getCount();
			}
			$inv->clearAll();
 		}else{
 			$this->gmc[$n] = $wiw;
 			$wiw = [];
		}
		$this->wi[$n]["Worlds"][$wn] = $wiw;
		$this->saveYml();
		$p->sendMessage("[WorldInventory] " . ($this->isKorean() ? "인벤토리가 교체되었습니다.": "Inventory is change ") . " : GameModeChange");
	}

	public function createInv($p,$wn){
		$n = strtolower($p->getName());
		$change = false;
		if(!isset($this->wi[$n])){
			$this->wi[$n] = ["LastWorld" => strtolower($p->getLevel()->getFolderName()), "Worlds" => []];
			$change = true;
 		}
		$wi = $this->wi[$n];
		if(!isset($wi["Worlds"])){
			$wi["Worlds"] = [];
			$change = true;
		}
		$wiw = $wi["Worlds"];
		if(!isset($wiw[$wn])){
			$wiw[$wn] = [];
			$change = true;
		}
		if(!isset($wi["LastWorld"])){
			$wi["LastWorld"] = $wn;
			$change = true;
		}
 		$wil = $wi["LastWorld"];
		if($change){
 			$this->wi[$n] = ["LastWorld" => $wn, "Worlds" => $wiw];
			$this->saveYml();
		}		
		return $change;
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->worldInventory = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "WorldInventory.yml", Config::YAML);
		$this->wi = $this->worldInventory->getAll();
	}

	public function saveYml(){
		foreach($this->wi as $wk => $wi){
			if(!isset($wi["Worlds"]) || !isset($wi["LastWorld"])){
				unset($this->wi[$wk]);
			}else{
				foreach($wi["Worlds"] as $k => $v){
					ksort($v);
					$this->wi[$k] = $v;
				}
			}
		}
		ksort($this->wi);
		$this->worldInventory->setAll($this->wi);
		$this->worldInventory->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}