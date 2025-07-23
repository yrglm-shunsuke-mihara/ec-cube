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
use Page\Front\ProductDetailPage;
use Page\Front\CartPage;

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
     * 同時購入競合テスト（シミュレーション）
     */
    public function stock_同時購入競合(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T03 在庫競合時の処理');
        
        $I->setStock(3, 1);
        
        $I->amOnPage('/products/detail/3');
        $I->click('.ec-productRole__btn');
        
        $I->sendPOST('/products/add_cart/3', [
            'product_id' => 3,
            'quantity' => 1
        ], [], ['HTTP_USER_AGENT' => 'TestUser2']);
        
        $I->seeResponseCodeIs(400);
    }

    /**
     * 在庫復活後の購入テスト
     */
    public function stock_在庫復活後購入(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T04 在庫復活後の購入処理');
        
        $I->setStock(4, 0);
        
        $I->amOnPage('/products/detail/4');
        $I->see('在庫切れ', '.ec-productRole__btn');
        
        $I->setStock(4, 10);
        
        $I->amOnPage('/products/detail/4');
        $I->see('カートに入れる', '.ec-productRole__btn');
        
        $I->click('.ec-productRole__btn');
        $I->see('カートに追加しました', '.ec-modal');
    }

    /**
     * 複数商品の在庫管理テスト
     */
    public function stock_複数商品在庫管理(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T05 複数商品の在庫管理');
        
        $I->setStock(5, 2);
        $I->setStock(6, 3);
        
        $I->amOnPage('/products/detail/5');
        $I->selectOption('select[name="quantity"]', '2');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/products/detail/6');
        $I->selectOption('select[name="quantity"]', '3');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->see('商品合計：2点');
        
        $I->amOnPage('/products/detail/5');
        $I->selectOption('select[name="quantity"]', '1');
        $I->click('.ec-productRole__btn');
        $I->see('在庫数が不足しています', '.ec-errorMessage');
    }
}
