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
        
        $productCount = 100;
        for ($i = 1; $i <= $productCount; $i++) {
            $I->haveInDatabase('dtb_product', [
                'id' => 1000 + $i,
                'name' => "パフォーマンステスト商品{$i}",
                'description_detail' => "パフォーマンステスト用の商品説明{$i}",
                'description_list' => "商品リスト説明{$i}",
                'price01' => 1000 + $i,
                'price02' => 900 + $i,
                'product_code' => "PERF{$i}",
                'sale_limit' => null,
                'delivery_date_id' => null,
                'create_date' => date('Y-m-d H:i:s'),
                'update_date' => date('Y-m-d H:i:s'),
                'Creator' => 1,
                'Status' => 1,
                'stock_find' => true
            ]);
            
            $I->haveInDatabase('dtb_product_class', [
                'product_id' => 1000 + $i,
                'ClassCategory1' => null,
                'ClassCategory2' => null,
                'price01' => 1000 + $i,
                'price02' => 900 + $i,
                'stock' => 10,
                'stock_unlimited' => false,
                'sale_limit' => null,
                'delivery_date_id' => null,
                'visible' => true,
                'create_date' => date('Y-m-d H:i:s'),
                'update_date' => date('Y-m-d H:i:s'),
                'Creator' => 1
            ]);
        }
        
        $startTime = microtime(true);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $I->see('商品管理', 'h1');
        $I->see('商品一覧', '.c-contentsArea__cols');
        
        $I->assertLessThan(10.0, $loadTime, "商品一覧の表示時間が{$loadTime}秒で許容範囲を超えています");
        
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
        
        $I->haveInDatabase('dtb_customer', [
            'id' => 9999,
            'name01' => 'パフォーマンス',
            'name02' => 'テスト',
            'email' => 'perf-test@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'salt' => null,
            'secret_key' => 'test_secret_key',
            'first_buy_date' => date('Y-m-d H:i:s'),
            'last_buy_date' => date('Y-m-d H:i:s'),
            'buy_times' => 50,
            'buy_total' => 500000,
            'note' => 'パフォーマンステスト用顧客',
            'Status' => 1,
            'create_date' => date('Y-m-d H:i:s'),
            'update_date' => date('Y-m-d H:i:s')
        ]);
        
        $orderCount = 50;
        for ($i = 1; $i <= $orderCount; $i++) {
            $orderDate = date('Y-m-d H:i:s', strtotime("-{$i} days"));
            
            $I->haveInDatabase('dtb_order', [
                'id' => 2000 + $i,
                'pre_order_id' => 'perf' . (2000 + $i),
                'order_no' => 2000 + $i,
                'message' => "パフォーマンステスト注文{$i}",
                'name01' => 'パフォーマンス',
                'name02' => 'テスト',
                'email' => 'perf-test@example.com',
                'phone_number' => '03-1234-5678',
                'postal_code' => '100-0001',
                'addr01' => '東京都千代田区',
                'addr02' => '1-1-1',
                'birth' => '1990-01-01',
                'subtotal' => 1000 * $i,
                'discount' => 0,
                'delivery_fee_total' => 500,
                'charge' => 0,
                'tax' => 100 * $i,
                'total' => 1000 * $i + 500 + 100 * $i,
                'payment_total' => 1000 * $i + 500 + 100 * $i,
                'payment_method' => 'クレジットカード',
                'note' => "パフォーマンステスト注文メモ{$i}",
                'create_date' => $orderDate,
                'update_date' => $orderDate,
                'order_date' => $orderDate,
                'payment_date' => $orderDate,
                'Currency' => 1,
                'Customer' => 9999,
                'Country' => 392,
                'Pref' => 13,
                'Sex' => 1,
                'Job' => 1,
                'Payment' => 1,
                'DeviceType' => 1,
                'OrderStatus' => rand(1, 5)
            ]);
        }
        
        $startTime = microtime(true);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/order');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $I->see('受注管理', 'h1');
        $I->see('受注一覧', '.c-contentsArea__cols');
        
        $I->assertLessThan(15.0, $loadTime, "受注一覧の表示時間が{$loadTime}秒で許容範囲を超えています");
        
        $I->fillField('admin_search_order[multi]', 'パフォーマンステスト');
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
        
        $startTime = microtime(true);
        
        for ($i = 1; $i <= 5; $i++) {
            $I->amOnPage("/products/detail/{$i}");
            if ($I->seeElement('.ec-productRole__btn')) {
                $I->click('.ec-productRole__btn');
                $I->wait(1); // 処理完了を待つ
            }
        }
        
        $endTime = microtime(true);
        $addToCartTime = $endTime - $startTime;
        
        $I->assertLessThan(10.0, $addToCartTime, "カート追加処理時間が{$addToCartTime}秒で許容範囲を超えています");
        
        $startTime = microtime(true);
        
        $I->amOnPage('/cart');
        
        $endTime = microtime(true);
        $cartLoadTime = $endTime - $startTime;
        
        $I->assertLessThan(3.0, $cartLoadTime, "カートページ表示時間が{$cartLoadTime}秒で許容範囲を超えています");
        
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
