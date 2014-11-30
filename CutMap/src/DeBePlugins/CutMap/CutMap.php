<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CutMap;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use pocketmine\block\Block;

class CutMap extends PluginBase implements Listener{

	public function onEnable(){
		$this->pla = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getMap();
 	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$ik = $this->isKorean();
		if($sender->getName() == "CONSOLE"){
			$r = $ik ? "게임내에서만 실행해주세요.": "Please run this command in-game";
		}else{
			$this->pla[$sender->getName()] = true;
			$r = $ik ? " [CutMap] 중심점이될 블럭을 터치해주세요. 맵을 붙여넣습니다.": " [CutMap] Touch the Center Block -> Paste Map";
		}
		$sender->sendMessage($r);
		return true;
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$p = $event->getPlayer();
		$n = $p->getName();
		$ik = $this->isKorean();
		$mm = "[CutMap] ";
		if(isset($this->pla[$n])){
			$b = $event->getBlock();
			$l = $b->getLevel();
			$list = $this->getMap();
			$c = count($list);
			$p->sendMessage($mm . ($ik ? "맵 붙여넣기 시작합니다!  ($c 개 블럭)": "Start paste Map!  ($c block)"));
			$tt = microtime(true);
			foreach($list as $lM){
				$m = explode(" ", $lM);
				$l->setBlock(new Position($b->getX() + $m[0], $b->getY() + $m[1], $b->getZ() + $m[2], $l), Block::get($m[3], $m[4]));
			}
			$tt = round((microtime(true) - $tt) * 100) / 100;
			$p->sendMessage($mm . ($ik ? "맵을 붙여넣기했습니다.  ($c 블럭) \n   (시간 : $tt 초)": "Complete paste Map!  ($c block) \n   (Time : $tt sec)"));
			$event->setCancelled(true);
			unset($this->pla[$n]);
		}
	}

	public function getMap(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "CutMap.yml", Config::YAML, []))->getAll();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}