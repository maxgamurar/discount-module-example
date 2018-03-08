<?php

class cDiscounts {

  private $discountRules = [];

  public function __construct() {
    
  }

  public function addRule(discountRule $discountRule) {
    $this->discountRules[] = $discountRule;
  }

  public function applyDiscounts(productsCart &$pC) {
    // apply rule according to its order list
    foreach ($this->discountRules as $dRule) {

      $productIDsSet = cartesianProductIDs::build($dRule->getProductsIDs());

      if ($dRule->getRuleType() == discountRule::RULE_EXACT_PRODUCT) {

        foreach ($productIDsSet as $pIDs) {

          // find if we have more than 1 set of products in cart
          $setQTY = 0;
          foreach ($pIDs as $pID) {
            if ($setQTY == 0 || ($setQTY > 0 && $setQTY > $pC->getProductQty($pID))) {
              $setQTY = $pC->getProductQty($pID);
            }
          }

          // find exact products by ID in cart
          if ($pC->matchProductsNotDiscounted($pIDs)) {
            // apply discount
            foreach ($pIDs as $pID) {
              $pC->setDiscount($pID, $dRule->getRuleDiscount(), $setQTY);
            }
          }
        }
      } elseif ($dRule->getRuleType() == discountRule::RULE_ANY_PRODUCT) {
        $pCartItems = $pC->getProducts();

        foreach ($pCartItems as $pCartItem) {

          // skip excluded from discount products
          if (!in_array($pCartItem['product']->getID(), $dRule->getExcludedPIDs())) {
            // find qty in discount table and set
            $discountValue = $dRule->getRuleTableDiscountbyQTY($pCartItem['qty']);
            if ($discountValue > 0) {
              $pC->setDiscount($pCartItem['product']->getID(), $discountValue);
            }
          }
        }
      }
    }
  }

}

class discountRule {

  const RULE_EXACT_PRODUCT = 1;
  const RULE_ANY_PRODUCT   = 2;

  private $productsIncludeIDs           = [];
  private $productsIncludeIDsSimplified = [];
  private $productsExcludeIDs           = [];
  /*
   * One of RULE_EXACT_PRODUCT or RULE_ANY_PRODUCT
   *
   */
  private $ruleType;
  // in percents %
  private $discountPercentage           = 0;
  // format: [quantity, discount, quantity2, discount2,..]
  private $discountTable                = [];
  private $discountTableNormalized      = [];

  public function __construct($ruleType, $discountPercentage, $discountTable = [], $productsIncl = [], $productsExcl = []) {

    $this->ruleType           = $ruleType;
    $this->discountPercentage = $discountPercentage;
    $this->discountTable      = $discountTable;
    $this->productsIncludeIDs = $productsIncl;

    if (count($this->productsIncludeIDs) > 1) {
      $this->productsIncludeIDsSimplified = [$this->productsIncludeIDs];
    }

    $this->productsExcludeIDs = $productsExcl;

    // basic check discount percentage is > 0
    if (!count($discountTable) && (!is_numeric($this->discountPercentage) || $this->discountPercentage <= 0)) {
      return false;
    }

    if (count($discountTable)) {

      // table discounts: parse table discounts data and normalize
      $discountTableData  = $this->discountTable;
      $tableDiscountsNorm = [];
      $curQty             = 0;
      for ($i = 0; $i < count($discountTableData); $i++) {
        if ($i % 2) {
          $tableDiscountsNorm[$curQty] = $discountTableData[$i];
        } else {
          $curQty = $discountTableData[$i];
        }
      }
      $this->discountTableNormalized = $tableDiscountsNorm;
    }

    // check if rule type is known
    if ($ruleType !== self::RULE_EXACT_PRODUCT && $ruleType !== self::RULE_ANY_PRODUCT) {
      return false;
    }

    return true;
  }

  public function getRuleType() {
    return $this->ruleType;
  }

  public function getRuleDiscount() {
    return $this->discountPercentage;
  }

  public function getDiscountTable() {
    return $this->discountTable;
  }

  public function getExcludedPIDs() {
    return $this->productsExcludeIDs;
  }

  public function getProductsIDs() {
    return $this->productsIncludeIDs;
  }

  public function getRuleTableDiscountbyQTY($qty) {
    $maxDiscountVal = 0;
    foreach ($this->discountTableNormalized as $qtyIndex => $dicountVal) {
      if ($qty >= $qtyIndex) {
        $maxDiscountVal = $dicountVal;
      }
    }
    return $maxDiscountVal;
  }

}
