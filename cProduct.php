<?php

class cProduct {

  private $ID;
  private $name;
  private $price;

  public function __construct($ID, $name, $price) {
    $this->ID    = $ID;
    $this->name  = $name;
    $this->price = $price;
  }

  public function getID() {
    return $this->ID;
  }

  public function getName() {
    return $this->name;
  }

}

class productsCart {

  // [products, qty, discount, discount_applied]
  private $productsList = [];

  public function __construct() {
    
  }

  public function add(cProduct $p, $qty = 1) {
    $pID = $p->getID();

    // check qty
    if (!is_numeric($qty) || $qty < 1) {
      $qty = 1;
    } else {
      $qty = (int) $qty;
    }

    if (isset($this->productsList[$pID])) {
      $this->productsList[$pID]['qty'] += $qty;
    } else {
      $this->productsList[$pID]['product']          = $p;
      $this->productsList[$pID]['qty']              = $qty;
      $this->productsList[$pID]['discount_applied'] = false;
      $this->productsList[$pID]['discount']         = 0;
    }
    return $this;
  }

  public function isDiscountApplied($pID) {
    return $this->productsList[$pID]['discount_applied'];
  }

  public function setDiscount($pID, $discountValue, $setQty = 0) {
    $this->productsList[$pID]['discount_applied'] = true;
    $this->productsList[$pID]['discount']         = $discountValue;
    $this->productsList[$pID]['discount_qty']     = $setQty > 1 ? $setQty : 1;
  }

  public function getProductQty($pID) {
    if (isset($this->productsList[$pID])) {
      return $this->productsList[$pID]['qty'];
    }

    return 0;
  }

  public function matchProductsNotDiscounted(array $pIDs) {

    $productsCount      = count($pIDs);
    $foundProductsCount = 0;

    foreach ($pIDs as $pID) {
      if (isset($this->productsList[$pID]) && !$this->isDiscountApplied($pID)) {
        $foundProductsCount++;
      }
    }

    return $productsCount === $foundProductsCount;
  }

  public function getProducts() {
    return $this->productsList;
  }

}

class cartesianProductIDs {

  public static function build($set) {
    if (!$set) {
      return [[]];
    }
    $subset = array_shift($set);
    if (!is_array($subset)) {
      $subset = [$subset];
    }
    $cartesianSubset = self::build($set);
    $result          = array();
    foreach ($subset as $value) {
      foreach ($cartesianSubset as $p) {
        array_unshift($p, $value);
        $result[] = $p;
      }
    }
    return $result;
  }

}
