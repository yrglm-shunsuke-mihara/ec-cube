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
use Page\Admin\ProductManagePage;
use Page\Admin\OrderManagePage;

/**
 * @group performance
 * @group load
 */
class PERF01PerformanceCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * 大量商品データでの一覧表示性能テスト
     */
    public function load_大量商品表示(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T01 大量商品データでの一覧表示性能');
        
        $createProduct = Fixtures::get('createProduct');
        $productCount = 20; // Reduced count for CI stability
        
        for ($i = 1; $i <= $productCount; $i++) {
            $createProduct("パフォーマンステスト商品{$i}", 1);
        }
        
        $startTime = microtime(true);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $I->see('商品管理', 'h1');
        $I->see('商品一覧', '.c-contentsArea__cols');
        
        $I->assertLessThan(15.0, $loadTime, "商品一覧の表示時間が{$loadTime}秒で許容範囲を超えています");
        
        if ($I->seeElement('.c-pagination')) {
            $I->click('.c-pagination .page-link');
            $I->see('商品管理', 'h1');
        }
    }

    /**
     * 大量注文データでの管理画面性能テスト
     */
    public function load_大量注文処理(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T02 大量注文データでの管理画面性能');
        
        $createCustomer = Fixtures::get('createCustomer');
        $createOrders = Fixtures::get('createOrders');
        
        $customer = $createCustomer('perf-test@example.com');
        
        $orderCount = 10; // Reduced count for CI stability
        $createOrders($customer, $orderCount);
        
        $startTime = microtime(true);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/order');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $I->see('受注管理', 'h1');
        $I->see('受注一覧', '.c-contentsArea__cols');
        
        $I->assertLessThan(20.0, $loadTime, "受注一覧の表示時間が{$loadTime}秒で許容範囲を超えています");
        
        $I->fillField('admin_search_order[multi]', $customer->getEmail());
        $I->click('検索');
        $I->see('受注管理', 'h1');
    }

    /**
     * フロント商品一覧ページの性能テスト
     */
    public function load_フロント商品一覧性能(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T03 フロント商品一覧ページの性能');
        
        $I->logoutAsAdmin();
        
        $startTime = microtime(true);
        
        $I->amOnPage('/products/list');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $I->see('商品一覧', 'h1');
        
        $I->assertLessThan(8.0, $loadTime, "フロント商品一覧の表示時間が{$loadTime}秒で許容範囲を超えています");
        
        $startTime = microtime(true);
        
        $I->selectOption('select[name="orderby"]', '2'); // 価格順
        $I->click('並び替え');
        
        $endTime = microtime(true);
        $sortTime = $endTime - $startTime;
        
        $I->assertLessThan(5.0, $sortTime, "商品ソートの処理時間が{$sortTime}秒で許容範囲を超えています");
    }

    /**
     * カート処理の性能テスト
     */
    public function load_カート処理性能(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T04 カート処理の性能');
        
        $I->logoutAsAdmin();
        
        $createProduct = Fixtures::get('createProduct');
        $products = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $products[] = $createProduct("カートテスト商品{$i}", 1);
        }
        
        $startTime = microtime(true);
        
        foreach ($products as $product) {
            $I->amOnPage("/products/detail/" . $product->getId());
            if ($I->seeElement('.ec-productRole__btn')) {
                $I->click('.ec-productRole__btn');
                $I->wait(1); // 処理完了を待つ
            }
        }
        
        $endTime = microtime(true);
        $addToCartTime = $endTime - $startTime;
        
        $I->assertLessThan(15.0, $addToCartTime, "カート追加処理時間が{$addToCartTime}秒で許容範囲を超えています");
        
        $startTime = microtime(true);
        
        $I->amOnPage('/cart');
        
        $endTime = microtime(true);
        $cartLoadTime = $endTime - $startTime;
        
        $I->assertLessThan(5.0, $cartLoadTime, "カートページ表示時間が{$cartLoadTime}秒で許容範囲を超えています");
        
        $I->see('ショッピングカート', 'h1');
    }

    /**
     * データベースクエリ性能テスト
     */
    public function load_データベースクエリ性能(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T05 データベースクエリ性能');
        
        $config = Fixtures::get('config');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/'.$config['eccube_admin_route'].'/order');
        
        $I->fillField('admin_search_order[multi]', 'テスト');
        $I->selectOption('admin_search_order[status][]', '1');
        $I->fillField('admin_search_order[order_date_start]', date('Y-m-d', strtotime('-30 days')));
        $I->fillField('admin_search_order[order_date_end]', date('Y-m-d'));
        $I->click('検索');
        
        $endTime = microtime(true);
        $searchTime = $endTime - $startTime;
        
        $I->assertLessThan(20.0, $searchTime, "複合検索処理時間が{$searchTime}秒で許容範囲を超えています");
        
        $I->see('受注管理', 'h1');
    }
}
