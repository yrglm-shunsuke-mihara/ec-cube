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

/**
 * @group admin
 * @group security
 * @group csrf
 */
class SEC01CSRFCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * CSRF攻撃対策テスト - 管理画面商品編集
     */
    public function csrf_管理画面商品編集(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T01 CSRF攻撃対策 - 管理画面商品編集');
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product/product/1/edit');
        
        $csrfToken = $I->grabValueFrom('input[name="_token"]');
        
        $I->sendPOST('/'.$config['eccube_admin_route'].'/product/product/1/edit', [
            '_token' => 'invalid_token',
            'name' => 'ハッキング商品名',
            'description_detail' => 'ハッキングされた商品説明'
        ]);
        
        $I->seeResponseCodeIs(403);
        
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product/product/1/edit');
        $validToken = $I->grabValueFrom('input[name="_token"]');
        
        $I->sendPOST('/'.$config['eccube_admin_route'].'/product/product/1/edit', [
            '_token' => $validToken,
            'name' => '正常な商品名',
            'description_detail' => '正常な商品説明'
        ]);
        
        $I->seeResponseCodeIs(302); // リダイレクト成功
    }

    /**
     * CSRF攻撃対策テスト - フロント会員登録
     */
    public function csrf_フロント会員登録(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T02 CSRF攻撃対策 - フロント会員登録');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/entry');
        
        $I->sendPOST('/entry', [
            '_token' => 'invalid_token',
            'name' => [
                'name01' => $faker->lastName,
                'name02' => $faker->firstName
            ],
            'email' => [
                'first' => $faker->safeEmail,
                'second' => $faker->safeEmail
            ],
            'password' => [
                'first' => 'password123',
                'second' => 'password123'
            ],
            'agree_terms' => 1
        ]);
        
        $I->seeResponseCodeIs(403);
    }

    /**
     * CSRF攻撃対策テスト - 管理画面顧客編集
     */
    public function csrf_管理画面顧客編集(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T03 CSRF攻撃対策 - 管理画面顧客編集');
        
        $config = Fixtures::get('config');
        
        $I->amOnPage('/'.$config['eccube_admin_route'].'/customer/1/edit');
        
        $I->sendPOST('/'.$config['eccube_admin_route'].'/customer/1/edit', [
            '_token' => 'invalid_token',
            'name' => [
                'name01' => 'ハッキング',
                'name02' => '太郎'
            ],
            'email' => 'hacker@example.com'
        ]);
        
        $I->seeResponseCodeIs(403);
    }

    /**
     * CSRF攻撃対策テスト - 管理画面受注編集
     */
    public function csrf_管理画面受注編集(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T04 CSRF攻撃対策 - 管理画面受注編集');
        
        $config = Fixtures::get('config');
        
        $I->amOnPage('/'.$config['eccube_admin_route'].'/order/1/edit');
        
        $I->sendPOST('/'.$config['eccube_admin_route'].'/order/1/edit', [
            '_token' => 'invalid_token',
            'OrderStatus' => 3, // 発送済みに変更を試行
            'note' => 'ハッキングされたメモ'
        ]);
        
        $I->seeResponseCodeIs(403);
    }
}
