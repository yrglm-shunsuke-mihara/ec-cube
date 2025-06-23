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
 * @group performance
 * @group load
 * @group response
 */
class PERF01PerformanceCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * トップページ表示速度テスト
     */
    public function performance_トップページ表示速度(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T01 トップページ表示速度測定');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // ミリ秒に変換
        
        $I->assertTrue($responseTime < 3000, "トップページの表示時間が3秒を超えています: {$responseTime}ms");
        
        $I->see('新着商品', '.ec-secHeading');
        $I->seeElement('.ec-shelfGrid');
        
        $I->comment("トップページ表示時間: {$responseTime}ms");
    }

    /**
     * 商品一覧ページ表示速度テスト
     */
    public function performance_商品一覧ページ表示速度(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T02 商品一覧ページ表示速度測定');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/products/list');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 5000, "商品一覧ページの表示時間が5秒を超えています: {$responseTime}ms");
        
        $I->seeElement('.ec-shelfGrid__item');
        
        $I->comment("商品一覧ページ表示時間: {$responseTime}ms");
    }

    /**
     * 商品詳細ページ表示速度テスト
     */
    public function performance_商品詳細ページ表示速度(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T03 商品詳細ページ表示速度測定');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/products/detail/1');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 3000, "商品詳細ページの表示時間が3秒を超えています: {$responseTime}ms");
        
        $I->seeElement('.ec-productRole__title');
        $I->seeElement('.ec-productRole__price');
        $I->seeElement('.ec-productRole__btn');
        
        $I->comment("商品詳細ページ表示時間: {$responseTime}ms");
    }

    /**
     * 検索機能パフォーマンステスト
     */
    public function performance_検索機能パフォーマンス(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T04 検索機能パフォーマンス測定');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/');
        $I->fillField('.ec-headerSearch__input', 'テスト');
        $I->click('.ec-headerSearch__btn');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 5000, "検索処理時間が5秒を超えています: {$responseTime}ms");
        
        $I->see('検索結果', '.ec-searchnavRole__title');
        
        $I->comment("検索処理時間: {$responseTime}ms");
    }

    /**
     * カート追加処理パフォーマンステスト
     */
    public function performance_カート追加処理パフォーマンス(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T05 カート追加処理パフォーマンス測定');
        
        $I->amOnPage('/products/detail/1');
        
        $startTime = microtime(true);
        
        $I->click('.ec-productRole__btn');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 2000, "カート追加処理時間が2秒を超えています: {$responseTime}ms");
        
        $I->see('カートに追加しました', '.ec-modal');
        
        $I->comment("カート追加処理時間: {$responseTime}ms");
    }

    /**
     * 管理画面ログインパフォーマンステスト
     */
    public function performance_管理画面ログインパフォーマンス(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T06 管理画面ログインパフォーマンス測定');
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/');
        
        $startTime = microtime(true);
        
        $I->submitForm('#form1', [
            'login_id' => 'admin',
            'password' => 'password',
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 3000, "管理画面ログイン処理時間が3秒を超えています: {$responseTime}ms");
        
        $I->see('ホーム', '.c-contentsArea .c-pageTitle > .c-pageTitle__titles');
        
        $I->comment("管理画面ログイン処理時間: {$responseTime}ms");
    }

    /**
     * 大量データ処理パフォーマンステスト
     */
    public function performance_大量データ処理パフォーマンス(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T07 大量データ処理パフォーマンス測定');
        
        $I->loginAsAdmin();
        
        $startTime = microtime(true);
        
        $I->amOnPage('/admin/product');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 10000, "大量データ表示時間が10秒を超えています: {$responseTime}ms");
        
        $I->seeElement('.c-pagination');
        
        $I->comment("大量データ表示時間: {$responseTime}ms");
    }

    /**
     * 同時アクセス負荷テスト（シミュレーション）
     */
    public function performance_同時アクセス負荷テスト(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T08 同時アクセス負荷テスト（シミュレーション）');
        
        $totalTime = 0;
        $accessCount = 5; // 5回のアクセスをシミュレート
        
        for ($i = 0; $i < $accessCount; $i++) {
            $startTime = microtime(true);
            
            switch ($i % 3) {
                case 0:
                    $I->amOnPage('/');
                    break;
                case 1:
                    $I->amOnPage('/products/list');
                    break;
                case 2:
                    $I->amOnPage('/products/detail/1');
                    break;
            }
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            $totalTime += $responseTime;
            
            $I->comment("アクセス{$i}: {$responseTime}ms");
        }
        
        $averageTime = $totalTime / $accessCount;
        
        $I->assertTrue($averageTime < 5000, "平均レスポンス時間が5秒を超えています: {$averageTime}ms");
        
        $I->comment("平均レスポンス時間: {$averageTime}ms");
    }

    /**
     * メモリ使用量監視テスト
     */
    public function performance_メモリ使用量監視(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T09 メモリ使用量監視');
        
        $initialMemory = memory_get_usage(true);
        
        $I->amOnPage('/');
        $I->amOnPage('/products/list');
        $I->amOnPage('/products/detail/1');
        $I->amOnPage('/cart');
        
        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;
        
        $I->assertTrue($memoryIncrease < 50 * 1024 * 1024, "メモリ使用量の増加が50MBを超えています: " . ($memoryIncrease / 1024 / 1024) . "MB");
        
        $I->comment("メモリ使用量増加: " . ($memoryIncrease / 1024 / 1024) . "MB");
    }

    /**
     * データベースクエリパフォーマンステスト
     */
    public function performance_データベースクエリパフォーマンス(AcceptanceTester $I)
    {
        $I->wantTo('PERF0101-UC01-T10 データベースクエリパフォーマンス測定');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/products/list?category_id=1');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $I->assertTrue($responseTime < 5000, "データベースクエリを含むページ表示時間が5秒を超えています: {$responseTime}ms");
        
        $I->seeElement('.ec-shelfGrid__item');
        
        $I->comment("データベースクエリ含む表示時間: {$responseTime}ms");
    }
}
