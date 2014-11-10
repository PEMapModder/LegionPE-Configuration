<?php

namespace legionpe\config;

class Purchase{
	private $primaryId, $productId, $expiry;
	public function __construct($primaryId, $productId, $expiry){
		$this->primaryId = $primaryId;
		$this->productId = $productId;
		$this->expiry = $expiry;
	}
	public function getPrimaryId(){
		return $this->primaryId;
	}
	public function getProductId(){
		return $this->productId;
	}
	public function isExpired(){
		return time() <= $this->expiry;
	}
}
