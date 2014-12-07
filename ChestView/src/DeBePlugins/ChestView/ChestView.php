<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ChestView;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

class ChestView extends PluginBase implements Listener{

	public function onEnable(){
		$this->touch = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$n = $sender->getName();
		$mm = "[ChestView] ";
		$ik = $this->isKorean();
		if($n == "CONSOLE"){
			$sender->sendMessage($mm . ($ik ? "게임내에서만 사용해주세요.": "Please run this command in game"));
		}elseif(isset($this->touch[$n])){
			unset($this->touch[$n]);
			$sender->sendMessage($mm . ($ik ? "체스트뷰 꺼짐": "Disable ChestView"));
		}else{
			$this->touch[$n] = true;
			$sender->sendMessage($mm . ($ik ? "체스트뷰 켜짐 \n 상자를 터치해주세요.": "Enable ChestView \n Touch target chest"));
		}
		return true;
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$b = $event->getBlock();
		$p = $event->getPlayer();
		if(isset($this->touch[$p->getName()]) && $b->getID() == 54){
			$c = $p->getLevel()->getTile(new Vector3($b->getX(), $b->getY(), $b->getZ()));
			$cv = [];
			$m = " [ChestView] (" . $b->getX() . ":" . $b->getY() . ":" . $b->getZ() . ") \n";
			for($f = 0; $f < 27; $f++){
				$i = $c->getItem($f);
				if($i->getID() !== 0 && $i->getCount() > 0){
					$m .= $i . " (" . $i->getCount() . ")  ";
					if($f % 3 == 0) $m .= "\n";
				}
			}
			$p->sendMessage($m);
			unset($this->touch[$p->getName()]);
			$event->setCancelled();
		}
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}