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
        
        $I->executeJS('
            var form = document.querySelector("form[name=admin_product]");
            if (form) {
                var csrfToken = form.querySelector("input[name*=_token]");
                if (csrfToken) {
                    csrfToken.value = "invalid_csrf_token";
                }
            }
        ');
        
        $I->fillField('admin_product[name]', 'CSRF攻撃テスト商品');
        $I->click('登録');
        
        $I->see('不正なアクセスです', '.alert-danger');
        
        $I->dontSeeInDatabase('dtb_product', [
            'name' => 'CSRF攻撃テスト商品'
        ]);
    }

    /**
     * CSRF攻撃対策テスト - フロント会員登録
     */
    public function csrf_フロント会員登録(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T02 CSRF攻撃対策 - フロント会員登録');
        
        $I->logoutAsAdmin();
        
        $I->amOnPage('/entry');
        $I->see('会員登録', 'h1');
        
        $I->executeJS('
            var form = document.querySelector("form[name=entry]");
            if (form) {
                var csrfToken = form.querySelector("input[name*=_token]");
                if (csrfToken) {
                    csrfToken.value = "invalid_csrf_token";
                }
            }
        ');
        
        $I->fillField('entry[name][name01]', '攻撃');
        $I->fillField('entry[name][name02]', 'テスト');
        $I->fillField('entry[kana][kana01]', 'コウゲキ');
        $I->fillField('entry[kana][kana02]', 'テスト');
        $I->fillField('entry[email][first]', 'csrf-test@example.com');
        $I->fillField('entry[email][second]', 'csrf-test@example.com');
        $I->fillField('entry[postal_code]', '1000001');
        $I->selectOption('entry[address][pref]', '東京都');
        $I->fillField('entry[address][addr01]', '千代田区');
        $I->fillField('entry[address][addr02]', '1-1');
        $I->fillField('entry[phone_number]', '03-1234-5678');
        $I->fillField('entry[password][first]', 'password123');
        $I->fillField('entry[password][second]', 'password123');
        $I->selectOption('entry[sex]', '1');
        $I->selectOption('entry[birth][year]', '1990');
        $I->selectOption('entry[birth][month]', '1');
        $I->selectOption('entry[birth][day]', '1');
        $I->checkOption('entry[user_policy_check]');
        
        $I->click('同意して会員登録をする');
        
        $I->see('不正なアクセスです', '.alert-danger');
        
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
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/order/'.$orderId.'/edit');
        
        $I->see('受注登録・編集', 'h1');
        
        $I->executeJS('
            var form = document.querySelector("form[name=order]");
            if (form) {
                var csrfToken = form.querySelector("input[name*=_token]");
                if (csrfToken) {
                    csrfToken.value = "invalid_csrf_token";
                }
            }
        ');
        
        $I->fillField('order[name01]', 'CSRF攻撃者');
        $I->click('登録');
        
        $I->see('不正なアクセスです', '.alert-danger');
        
        $I->dontSeeInDatabase('dtb_order', [
            'id' => $orderId,
            'name01' => 'CSRF攻撃者'
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
        
        $I->amOnPage('/mypage/change_password');
        $I->see('パスワード変更', 'h1');
        
        $I->executeJS('
            var form = document.querySelector("form[name=change_password]");
            if (form) {
                var csrfToken = form.querySelector("input[name*=_token]");
                if (csrfToken) {
                    csrfToken.value = "invalid_csrf_token";
                }
            }
        ');
        
        $I->fillField('change_password[current_password]', 'password');
        $I->fillField('change_password[password][first]', 'newpassword123');
        $I->fillField('change_password[password][second]', 'newpassword123');
        $I->click('変更');
        
        $I->see('不正なアクセスです', '.alert-danger');
        
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
        $I->amOnPage('/'.$config['eccube_admin_route'].'/customer');
        
        $I->fillField('admin_search_customer[multi]', $customer->getEmail());
        $I->click('検索');
        
        $I->see($customer->getEmail(), '.c-contentsArea');
        
        $I->executeJS('
            var deleteForm = document.querySelector("form[data-message*=\"削除\"]");
            if (deleteForm) {
                var csrfToken = deleteForm.querySelector("input[name*=_token]");
                if (csrfToken) {
                    csrfToken.value = "invalid_csrf_token";
                }
                deleteForm.submit();
            }
        ');
        
        $I->wait(2);
        
        $I->see('不正なアクセスです', '.alert-danger');
        
        $I->seeInDatabase('dtb_customer', [
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
