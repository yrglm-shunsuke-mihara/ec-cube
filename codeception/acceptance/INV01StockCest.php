<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Codeception\Util\Fixtures;

/**
 * @group front
 * @group inventory
 * @group stock
 */
class INV01StockCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * 在庫切れ商品の購入試行テスト
     */
    public function stock_在庫切れ商品購入(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T01 在庫切れ商品の購入試行');
        
        $I->setStock(1, 0);
        
        $I->amOnPage('/products/detail/1');
        
        $I->dontSee('カートに入れる', '.ec-productRole__btn');
        $I->see('在庫切れ', '.ec-productRole__btn');
        
        $I->sendPOST('/products/add_cart/1', [
            'product_id' => 1,
            'quantity' => 1
        ]);
        
        $I->seeResponseCodeIs(400);
        
        $I->amOnPage('/cart');
        $I->see('現在カート内に商品はございません。');
    }

    /**
     * 在庫数を超える購入試行テスト
     */
    public function stock_在庫数超過購入(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T02 在庫数を超える購入試行');
        
        $I->setStock(2, 5);
        
        $I->amOnPage('/products/detail/2');
        
        $I->selectOption('select[name="quantity"]', '10');
        $I->click('.ec-productRole__btn');
        
        $I->see('在庫数が不足しています', '.ec-errorMessage');
        
        $I->selectOption('select[name="quantity"]', '3');
        $I->click('.ec-productRole__btn');
        $I->see('カートに追加しました', '.ec-modal');
    }

    /**
     * 在庫管理の整合性テスト
     */
    public function stock_在庫整合性確認(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T03 在庫管理の整合性確認');
        
        $I->setStock(3, 3);
        
        $I->amOnPage('/products/detail/3');
        $I->selectOption('select[name="quantity"]', '2');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->see('2', 'input[name="quantity"]');
        
        $I->fillField('input[name="quantity"]', '5');
        $I->click('.ec-cartRow__amountUpDown button[type="submit"]');
        
        $I->see('在庫数が不足しています', '.ec-errorMessage');
        
        $I->seeInField('input[name="quantity"]', '2');
    }

    /**
     * 複数商品の在庫競合テスト
     */
    public function stock_複数商品在庫競合(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T04 複数商品の在庫競合処理');
        
        $I->setStock(1, 2);
        $I->setStock(2, 1);
        
        $I->amOnPage('/products/detail/1');
        $I->selectOption('select[name="quantity"]', '2');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/products/detail/2');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->see('2', 'tr:nth-child(1) input[name="quantity"]');
        $I->see('1', 'tr:nth-child(2) input[name="quantity"]');
        
        $I->fillField('tr:nth-child(1) input[name="quantity"]', '5');
        $I->click('tr:nth-child(1) .ec-cartRow__amountUpDown button[type="submit"]');
        
        $I->see('在庫数が不足しています', '.ec-errorMessage');
        
        $I->seeInField('tr:nth-child(2) input[name="quantity"]', '1');
    }

    /**
     * 在庫復旧後の購入可能性テスト
     */
    public function stock_在庫復旧後購入(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T05 在庫復旧後の購入可能性');
        
        $I->setStock(4, 0);
        
        $I->amOnPage('/products/detail/4');
        $I->see('在庫切れ', '.ec-productRole__btn');
        
        $I->setStock(4, 5);
        
        $I->amOnPage('/products/detail/4');
        
        $I->see('カートに入れる', '.ec-productRole__btn');
        $I->click('.ec-productRole__btn');
        $I->see('カートに追加しました', '.ec-modal');
        
        $I->amOnPage('/cart');
        $I->see('1', 'input[name="quantity"]');
    }

    /**
     * 在庫無制限商品のテスト
     */
    public function stock_在庫無制限商品(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T06 在庫無制限商品の動作確認');
        
        $entityManager = Fixtures::get('entityManager');
        $product = $entityManager->getRepository('Eccube\Entity\Product')->find(5);
        if ($product) {
            $productClass = $product->getProductClasses()->first();
            $productClass->setStockUnlimited(true);
            $entityManager->flush();
            
            $I->amOnPage('/products/detail/5');
            
            $I->selectOption('select[name="quantity"]', '99');
            $I->click('.ec-productRole__btn');
            $I->see('カートに追加しました', '.ec-modal');
            
            $I->amOnPage('/cart');
            $I->see('99', 'input[name="quantity"]');
        }
    }
}
