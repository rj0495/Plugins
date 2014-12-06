<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CocoaBean;

use pocketmine\block\Flowable;

class CocoaBeanBlock extends Flowable{

	public function __construct($meta = 0){
		parent::__construct(127, $meta, "Cocoa Bean");
		$this->hardness = 0.5;
	}

	public function getBoundingBox(){
		return null;
	}
}