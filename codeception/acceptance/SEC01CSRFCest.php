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
 * @group admin
 * @group security
 * @group csrf
 */
class SEC01CSRFCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/admin/logout');
    }

    /**
     * CSRF攻撃対策テスト - 管理画面商品編集
     */
    public function csrf_管理画面商品編集(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T01 CSRF攻撃対策 - 管理画面商品編集');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/product/product/1/edit');
        
        $csrfToken = $I->grabValueFrom('input[name="_token"]');
        
        $I->sendPOST('/admin/product/product/1/edit', [
            '_token' => 'invalid_token',
            'name' => 'ハッキング商品名',
            'description_detail' => 'ハッキングされた商品説明'
        ]);
        
        $I->seeResponseCodeIs(403);
        
        $I->amOnPage('/admin/product/product/1/edit');
        $validToken = $I->grabValueFrom('input[name="_token"]');
        
        $I->sendPOST('/admin/product/product/1/edit', [
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
     * CSRF攻撃対策テスト - 管理画面顧客削除
     */
    public function csrf_管理画面顧客削除(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T03 CSRF攻撃対策 - 管理画面顧客削除');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/customer');
        
        $I->sendPOST('/admin/customer/1/delete', [
            '_token' => 'invalid_token'
        ]);
        
        $I->seeResponseCodeIs(403);
    }

    /**
     * CSRF攻撃対策テスト - 管理画面受注ステータス変更
     */
    public function csrf_管理画面受注ステータス変更(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T04 CSRF攻撃対策 - 管理画面受注ステータス変更');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/order');
        
        $I->sendPOST('/admin/order/1/edit', [
            '_token' => 'invalid_token',
            'OrderStatus' => 3 // 発送済み
        ]);
        
        $I->seeResponseCodeIs(403);
    }
}
