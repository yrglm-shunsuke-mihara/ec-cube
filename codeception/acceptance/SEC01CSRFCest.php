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
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('CSRFテスト商品', 1);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product/'.$product->getId().'/edit');
        
        $I->see('商品登録・編集', 'h1');
        
        $originalName = $I->grabValueFrom('admin_product[name]');
        
        $I->sendPOST('/'.$config['eccube_admin_route'].'/product/'.$product->getId().'/edit', [
            'admin_product' => [
                'name' => 'CSRF攻撃テスト商品',
                '_token' => 'invalid_csrf_token'
            ]
        ]);
        
        $I->dontSeeInDatabase('dtb_product', [
            'name' => 'CSRF攻撃テスト商品'
        ]);
        
        $I->seeInDatabase('dtb_product', [
            'id' => $product->getId(),
            'name' => $originalName
        ]);
    }

    /**
     * CSRF攻撃対策テスト - フロント会員登録
     */
    public function csrf_フロント会員登録(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T02 CSRF攻撃対策 - フロント会員登録');
        
        $I->logoutAsAdmin();
        
        $I->sendPOST('/entry', [
            'entry' => [
                'name' => [
                    'name01' => '攻撃',
                    'name02' => 'テスト'
                ],
                'kana' => [
                    'kana01' => 'コウゲキ',
                    'kana02' => 'テスト'
                ],
                'email' => [
                    'first' => 'csrf-test@example.com',
                    'second' => 'csrf-test@example.com'
                ],
                'postal_code' => '1000001',
                'address' => [
                    'pref' => '13',
                    'addr01' => '千代田区',
                    'addr02' => '1-1'
                ],
                'phone_number' => '03-1234-5678',
                'password' => [
                    'first' => 'password123',
                    'second' => 'password123'
                ],
                'sex' => '1',
                'birth' => [
                    'year' => '1990',
                    'month' => '1',
                    'day' => '1'
                ],
                'user_policy_check' => '1',
                '_token' => 'invalid_csrf_token'
            ]
        ]);
        
        $I->dontSeeInDatabase('dtb_customer', [
            'email' => 'csrf-test@example.com'
        ]);
    }

    /**
     * CSRF攻撃対策テスト - 管理画面受注編集
     */
    public function csrf_管理画面受注編集(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T03 CSRF攻撃対策 - 管理画面受注編集');
        
        $createCustomer = Fixtures::get('createCustomer');
        $createOrders = Fixtures::get('createOrders');
        
        $customer = $createCustomer('csrf-order-test@example.com');
        $createOrders($customer, 1);
        
        $orderId = $I->grabFromDatabase('dtb_order', 'id', [
            'Customer' => $customer->getId()
        ]);
        
        $originalName = $I->grabFromDatabase('dtb_order', 'name01', [
            'id' => $orderId
        ]);
        
        $config = Fixtures::get('config');
        $I->sendPOST('/'.$config['eccube_admin_route'].'/order/'.$orderId.'/edit', [
            'order' => [
                'name01' => 'CSRF攻撃者',
                '_token' => 'invalid_csrf_token'
            ]
        ]);
        
        $I->dontSeeInDatabase('dtb_order', [
            'id' => $orderId,
            'name01' => 'CSRF攻撃者'
        ]);
        
        $I->seeInDatabase('dtb_order', [
            'id' => $orderId,
            'name01' => $originalName
        ]);
    }

    /**
     * CSRF攻撃対策テスト - フロントパスワード変更
     */
    public function csrf_フロントパスワード変更(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T04 CSRF攻撃対策 - フロントパスワード変更');
        
        $I->logoutAsAdmin();
        
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer('csrf-password-test@example.com');
        
        $I->loginAsMember($customer->getEmail(), 'password');
        
        $I->sendPOST('/mypage/change_password', [
            'change_password' => [
                'current_password' => 'password',
                'password' => [
                    'first' => 'newpassword123',
                    'second' => 'newpassword123'
                ],
                '_token' => 'invalid_csrf_token'
            ]
        ]);
        
        $I->amOnPage('/mypage/login');
        $I->fillField('login_email', $customer->getEmail());
        $I->fillField('login_pass', 'newpassword123');
        $I->click('ログイン');
        
        $I->see('ログインできませんでした', '.alert-danger');
    }

    /**
     * CSRF攻撃対策テスト - 管理画面顧客削除
     */
    public function csrf_管理画面顧客削除(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T05 CSRF攻撃対策 - 管理画面顧客削除');
        
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer('csrf-delete-test@example.com');
        
        $config = Fixtures::get('config');
        $I->sendPOST('/'.$config['eccube_admin_route'].'/customer/'.$customer->getId().'/delete', [
            '_token' => 'invalid_csrf_token'
        ]);
        
        $I->seeInDatabase('dtb_customer', [
            'id' => $customer->getId(),
            'email' => $customer->getEmail()
        ]);
    }

    /**
     * 正常なCSRFトークンでの処理確認テスト
     */
    public function csrf_正常トークン確認(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T06 正常なCSRFトークンでの処理確認');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('正常CSRFテスト商品', 1);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product/'.$product->getId().'/edit');
        
        $I->see('商品登録・編集', 'h1');
        
        $I->fillField('admin_product[name]', '正常CSRF更新商品');
        $I->click('登録');
        
        $I->see('保存しました', '.alert-success');
        
        $I->seeInDatabase('dtb_product', [
            'name' => '正常CSRF更新商品'
        ]);
    }
}
