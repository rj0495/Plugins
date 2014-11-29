<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\PVPManager;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\entity\Arrow;

class PVPManager extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$ik = $this->isKorean();
		$pvp = $this->pvp;
		switch(strtolower($cmd)){
			case "pvp":
				if($pvp["PVP"]) $b = false;
				else $b = true;
				$pvp["PVP"] = $b;
				$m = "[PVP Manager] PvP" . ($b ? ($ik ? "가 켜졌습니다." : " is On") : ($ik ? "가 꺼졌습니다." : "is Off"));
			break;
			case "dmg":
				if($pvp["DMG"]) $b = false;
				else $b = true;
				$pvp["DMG"] = $b;
				$m = "[PVP Manager] " . ($b ? ($ik ? "모든 데미지가 켜졌습니다." : "All damage is On") : ($ik ? "모든 데미지가 꺼졌습니다." : "All damage is Off"));
			break;
			case "inv":
				if($pvp["Inv"]) $b = false;
				else $b = true;
				$pvp["Inv"] = $b;
				$m = "[PVP Manager] " . ($b ? ($ik ? "인벤세이브가 켜졌습니다." : "Invensave is On") : ($ik ? "인벤세이브가 꺼졌습니다." : "Invensave is Off"));
			break;
			default:
				return false;
			break;
		}
		$this->pvp = $pvp;
		$this->saveYml();
		$this->getServer()->broadCastMessage($m);
		return true;
	}

	public function onPlayerDeath(PlayerDeathEvent $event){
		if($this->pvp["Inv"]) $event->setKeepInventory(true);
	}

	public function onEntityDamage(EntityDamageEvent $event){
		if($this->pvp["DMG"] === false) $event->setCancelled();
		if($event->getEntity() instanceof Player && $this->pvp["PVP"] !== true && $event instanceof EntityDamageByEntityEvent){
			$dmg = $event->getDamager();
			if($dmg instanceof Player){
				if($dmg->hasPermission("debe.pvpmanager.pvp.attack")){
					$event->setCancelled(false);
				}else{
					$event->setCancelled();
					$dmg->sendMessage("[PVP Manager] PVP 권한이 없습니다.");
				}
			}
		}
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->pvp = (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "PVPManager.yml", Config::YAML,["PVP" => true, "DMG" => true, "Inv" => false]))->getAll();
	}

	public function saveYml(){
		$pvp = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "PVPManager.yml", Config::YAML);
		$pvp->setAll($this->pvp);
		$pvp->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}