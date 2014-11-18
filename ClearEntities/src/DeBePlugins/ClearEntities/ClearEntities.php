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

class ClearEntities extends PluginBase{

   public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
      if(!isset($sub[1])) return false;
      $mm = "[ClearEntities] ";
      $ik = $this->isKorean();
      switch(strtolower($sub[0])){
         case "item":
         case "i":
         case "아이템":
            $m = $ik ? "아이템": "Item";
         break;
         case "arrow":
         case "a":
         case "화살":
            $m = $ik ? "화살": "Arrow";
         break;
         case "monster":
         case "mob":
         case "m":
         case "몬스터":
            $m = $ik ? "몬스터": "Monster";
         break;
         default:
            $m = $ik ? "모든": "All";
         break;
      }
      $entities = [];
      foreach($this->getServer()->getLevels() as $l){
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
      switch(strtolower($sub[1])){
         case "view":
         case "v":
         case "보기":
            $r = $mm . ($c > 0 ? ($ik ? "이 서버에는 $c개의 $m 엔티티가 있습니다. \n": "This server has $c $m Entities. \n"): ($ik ? "이 서버에는$m 엔티티가 없습니다. \n": "This server don\'t has $m Entities. \n"));
         break;
         case "clear":
         case "c":
         case "클리어":
         case "초기화":
            foreach($entities as $e)
               $e->close();
            $r = $mm . ($ik ? "모든 $m 엔티티를 제거했습니다. : $c\n": " Clear all $m Entities : $c\n");
         break;
         default:
            return false;
         break;
      }
      if(isset($r)) $sender->sendMessage($r);
   		return true;
   }

   public function isKorean(){
      @mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
      return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
   }
}