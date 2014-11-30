<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ChatSwitch;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;

class ChatSwitch extends PluginBase implements Listener{

	public function onEnable(){
		$this->chat = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$mm = "[ChatSwitch] ";
		$n = $sender->getName();
		if(!isset($this->chat[$n])) $this->chat[$n] = true;
		if($this->chat[$n]) $s = false;
		else $s = true;
		$this->chat[$n] = $s;
		$sender->sendMessage((new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean") ? "채팅을 받" . ($s ? "" : "지 않") . "습니다." : ($s ? "" : "Not ") . "receive the chat");
		return true;
	}

	public function onPlayerChat(PlayerChatEvent $event){
		$recipients = $event->getRecipients();
		foreach($recipients as $k => $v){
			$n = $v->getName();
			if(!isset($this->chat[$n])) $this->chat[$n] = true;
			if(!$this->chat[$n]) unset($recipients[$k]);
		}
		$event->setRecipients($recipients);
	}
}