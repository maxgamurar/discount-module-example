<?php

/*
 * 
 *  This is an exampe of discount module with some complex rules
 *  with ability to easely add new rules based on product combinations
 *  or table of discounts per item quantity
 * 
 */

// helper product classes
include 'cProduct.php';

// main discount class
include 'cDiscounts.php';

// init products

$productsCart = new productsCart();

//  add few sample products
$productsCart
        ->add(new cProduct(1, 'A', 10), 3)
        ->add(new cProduct(2, 'B', 15))
        ->add(new cProduct(3, 'C', 20))
        ->add(new cProduct(4, 'D', 10), 4)
        ->add(new cProduct(5, 'E', 5), 6)
        ->add(new cProduct(6, 'F', 25))
        ->add(new cProduct(7, 'G', 5))
        ->add(new cProduct(8, 'H', 15))
        ->add(new cProduct(9, 'I', 25))
        ->add(new cProduct(10, 'J', 45))
        ->add(new cProduct(11, 'K', 10))
        ->add(new cProduct(12, 'L', 22))
        ->add(new cProduct(13, 'M', 33));

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Create and apply discount rules
// comment/uncomment each rule initialization to verify the logic
// 1 If both A and B are selected at the same time, their total cost is reduced by 10% (for each pair of A and B)
$discountMod = new cDiscounts();
//$discountMod->addRule(new discountRule(discountRule::RULE_EXACT_PRODUCT, 10, [], [1, 2]));
// If D and E are simultaneously selected, their total cost is reduced by 5% (for each pair of D and E)
//$discountMod->addRule(new discountRule(discountRule::RULE_EXACT_PRODUCT, 5, [], [4, 5]));
// If E, F, G are simultaneously selected, their total cost is reduced by 5% (for each triplet E, F, G);
//$discountMod->addRule(new discountRule(discountRule::RULE_EXACT_PRODUCT, 5, [], [5, 6, 7]));
// If both A and one of [K, L, M] are selected at the same time, the cost of the selected product is reduced by 5%;
//$discountMod->addRule(new discountRule(discountRule::RULE_EXACT_PRODUCT, 5, [], [1, [11, 12, 13]]));
/*
 * If the user chooses 3 products at the same time, he receives a 5% discount on the order amount;
 * If the user chooses 4 products at the same time, he receives a 10% discount on the order amount;
 * If the user chooses 5 products at the same time, he receives a 20% discount on the order amount;
 * The described discounts 5,6,7 are not summarized, only one of them is applied;
 * Products A and C do not participate in discounts of 5.6.7;
 * 
 */
$discountMod->addRule(new discountRule(discountRule::RULE_ANY_PRODUCT, 0, [3, 5, 4, 10, 5, 20], [], [1, 3]));


////////////////////
// apply discounts
$discountMod->applyDiscounts($productsCart);


// display product cart content with discounts
echo '<pre>' . var_export($productsCart->getProducts(), 1) . '</pre>';


