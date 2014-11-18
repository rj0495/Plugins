<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\Korean;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class Korean extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$korean = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]);
		if($korean->get("Korean")){
			$korean->set("Korean", false);
			$m = "설정";
		}else{
			$korean->set("Korean", true);
			$m = "해제";
		}
		$sender->sendMessage("[Korean] 한국말 $m");
		$korean->save();
		return true;
	}
}