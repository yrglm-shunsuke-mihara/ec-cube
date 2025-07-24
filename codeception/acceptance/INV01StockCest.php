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
 * @group inventory
 * @group stock
 * @group edge-case
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
    public function stock_在庫切れ商品購入試行(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T01 在庫切れ商品の購入試行');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('在庫切れテスト商品', 1);
        
        $I->setStock($product->getId(), 0);
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->see('在庫切れテスト商品', 'h1');
        
        $I->dontSeeElement('.ec-productRole__btn');
        $I->see('ただいま品切れ中です', '.ec-productRole__out-of-stock');
        
        $I->amOnPage('/cart');
        $I->see('現在カート内に商品はございません', '.ec-cartRole__progress');
    }

    /**
     * 在庫数を超える購入試行テスト
     */
    public function stock_在庫数超過購入試行(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T02 在庫数を超える購入試行');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('在庫制限テスト商品', 1);
        
        $stockLimit = 3;
        $I->setStock($product->getId(), $stockLimit);
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->see('在庫制限テスト商品', 'h1');
        
        $I->fillField('#quantity', $stockLimit + 1);
        $I->click('.ec-productRole__btn');
        
        $I->see('在庫が不足しています', '.ec-alert-warning');
        
        $I->fillField('#quantity', $stockLimit);
        $I->click('.ec-productRole__btn');
        
        $I->see('カートに追加しました', '.ec-modal');
        
        $I->amOnPage('/cart');
        $I->see('在庫制限テスト商品', '.ec-cartRow__name');
        $I->seeInField('.ec-cartRow__amountUpDown input', $stockLimit);
    }

    /**
     * 在庫数境界値テスト
     */
    public function stock_在庫境界値テスト(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T03 在庫数境界値テスト');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('境界値テスト商品', 1);
        
        $stockLimit = 1;
        $I->setStock($product->getId(), $stockLimit);
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->see('境界値テスト商品', 'h1');
        
        $I->fillField('#quantity', $stockLimit);
        $I->click('.ec-productRole__btn');
        
        $I->see('カートに追加しました', '.ec-modal');
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->dontSeeElement('.ec-productRole__btn');
        $I->see('ただいま品切れ中です', '.ec-productRole__out-of-stock');
    }

    /**
     * カート内での在庫数変更テスト
     */
    public function stock_カート内在庫数変更(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T04 カート内での在庫数変更');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('カート在庫テスト商品', 1);
        
        $stockLimit = 5;
        $I->setStock($product->getId(), $stockLimit);
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->fillField('#quantity', 2);
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->see('カート在庫テスト商品', '.ec-cartRow__name');
        
        $I->fillField('.ec-cartRow__amountUpDown input', $stockLimit + 1);
        $I->click('.ec-cartRow__amountUpDown .ec-plusBtn');
        
        $I->see('在庫が不足しています', '.ec-alert-warning');
        
        $I->fillField('.ec-cartRow__amountUpDown input', $stockLimit);
        $I->click('.ec-cartRow__amountUpDown .ec-plusBtn');
        
        $I->seeInField('.ec-cartRow__amountUpDown input', $stockLimit);
    }

    /**
     * 複数商品の在庫管理テスト
     */
    public function stock_複数商品在庫管理(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T05 複数商品の在庫管理');
        
        $createProduct = Fixtures::get('createProduct');
        $product1 = $createProduct('複数在庫テスト商品1', 1);
        $product2 = $createProduct('複数在庫テスト商品2', 1);
        
        $I->setStock($product1->getId(), 2);
        $I->setStock($product2->getId(), 3);
        
        $I->amOnPage('/products/detail/' . $product1->getId());
        $I->fillField('#quantity', 2);
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/products/detail/' . $product2->getId());
        $I->fillField('#quantity', 3);
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->see('複数在庫テスト商品1', '.ec-cartRow__name');
        $I->see('複数在庫テスト商品2', '.ec-cartRow__name');
        
        $I->click('.ec-blockBtn--action');
        
        $I->see('ご注文手続き', 'h1');
    }

    /**
     * 在庫復活後の購入可能性テスト
     */
    public function stock_在庫復活購入テスト(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T06 在庫復活後の購入可能性');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('在庫復活テスト商品', 1);
        
        $I->setStock($product->getId(), 0);
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->dontSeeElement('.ec-productRole__btn');
        $I->see('ただいま品切れ中です', '.ec-productRole__out-of-stock');
        
        $I->setStock($product->getId(), 5);
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->seeElement('.ec-productRole__btn');
        $I->dontSee('ただいま品切れ中です');
        
        $I->fillField('#quantity', 2);
        $I->click('.ec-productRole__btn');
        
        $I->see('カートに追加しました', '.ec-modal');
    }

    /**
     * 管理画面での在庫設定テスト
     */
    public function stock_管理画面在庫設定(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T07 管理画面での在庫設定');
        
        $I->loginAsAdmin();
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('管理画面在庫テスト商品', 1);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product/'.$product->getId().'/edit');
        
        $I->see('商品登録・編集', 'h1');
        
        $I->fillField('admin_product[ProductClasses][0][stock]', '10');
        $I->click('登録');
        
        $I->see('保存しました', '.alert-success');
        
        $I->seeInDatabase('dtb_product_stock', [
            'stock' => 10
        ]);
        
        $I->logoutAsAdmin();
        
        $I->amOnPage('/products/detail/' . $product->getId());
        $I->seeElement('.ec-productRole__btn');
        
        $I->fillField('#quantity', 10);
        $I->click('.ec-productRole__btn');
        
        $I->see('カートに追加しました', '.ec-modal');
    }
}
