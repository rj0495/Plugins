<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\LocalChat;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;

class LocalChat extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$mm = "[LocalChat] ";
		$dc = $this->distanceChat->getAll();
		if($sub[0] < 1 || !is_numeric($sub[0])) $sub[0] = 1;
		$sender->sendMessage($this->isKorean() ? "채팅 거리가 [$sub[0]] 로 설정되었습니다.": "Chat range is set [$sub[0]]");
		$this->distanceChat->set("Local", $sub[0]);
		$this->saveYml();
		return true;
	}

	public function onPlayerChat(PlayerChatEvent $event){
		$recipients = $event->getRecipients();
		foreach($recipients as $k => $v){
			if($v instanceof Player){
				if($event->getPlayer()->getLevel() !== $v->getLevel() || $event->getPlayer()->distance($v) > $this->distanceChat->get("Local")) unset($recipients[$k]);
				}
		}
		$event->setRecipients($recipients);
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this-> distanceChat = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "LocalChat.yml", Config::YAML, ["Local" => 100 ]);
	}

	public function saveYml(){
		$this->distanceChat->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}