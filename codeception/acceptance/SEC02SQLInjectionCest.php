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
 * @group sql_injection
 */
class SEC02SQLInjectionCest
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
     * SQLインジェクション対策テスト - 管理画面商品検索
     */
    public function sql_injection_管理画面商品検索(AcceptanceTester $I)
    {
        $I->wantTo('SEC0201-UC01-T01 SQLインジェクション対策 - 管理画面商品検索');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/product');
        
        $maliciousInput = "'; DROP TABLE dtb_product; --";
        
        $I->fillField('input[name="id"]', $maliciousInput);
        $I->click('検索');
        
        $I->dontSee('SQL syntax error');
        $I->dontSee('MySQL server has gone away');
        $I->dontSee('Table \'dtb_product\' doesn\'t exist');
        
        $I->amOnPage('/admin/product');
        $I->see('商品管理');
    }

    /**
     * SQLインジェクション対策テスト - フロント商品検索
     */
    public function sql_injection_フロント商品検索(AcceptanceTester $I)
    {
        $I->wantTo('SEC0201-UC01-T02 SQLインジェクション対策 - フロント商品検索');
        
        $I->amOnPage('/');
        
        $maliciousInputs = [
            "' OR 1=1 --",
            "'; SELECT * FROM dtb_customer; --",
            "' UNION SELECT password FROM dtb_customer WHERE id=1 --",
            "1' AND (SELECT COUNT(*) FROM dtb_customer) > 0 --"
        ];
        
        foreach ($maliciousInputs as $input) {
            $I->fillField('input[name="name"]', $input);
            $I->click('検索');
            
            $I->dontSee('SQL syntax error');
            $I->dontSee('MySQL server has gone away');
            $I->dontSee('password');
            $I->dontSee('dtb_customer');
            
            $I->amOnPage('/');
        }
    }

    /**
     * SQLインジェクション対策テスト - 管理画面顧客検索
     */
    public function sql_injection_管理画面顧客検索(AcceptanceTester $I)
    {
        $I->wantTo('SEC0201-UC01-T03 SQLインジェクション対策 - 管理画面顧客検索');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/customer');
        
        $maliciousInputs = [
            "' OR '1'='1",
            "'; UPDATE dtb_customer SET email='hacked@example.com' WHERE id=1; --",
            "' UNION SELECT id,email,password FROM dtb_customer --"
        ];
        
        foreach ($maliciousInputs as $input) {
            $I->fillField('input[name="email"]', $input);
            $I->click('検索');
            
            $I->dontSee('SQL syntax error');
            $I->dontSee('hacked@example.com');
            $I->see('顧客管理');
            
            $I->amOnPage('/admin/customer');
        }
    }

    /**
     * SQLインジェクション対策テスト - 注文検索
     */
    public function sql_injection_注文検索(AcceptanceTester $I)
    {
        $I->wantTo('SEC0201-UC01-T04 SQLインジェクション対策 - 注文検索');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/order');
        
        $maliciousInput = "1' OR (SELECT COUNT(*) FROM dtb_order) > 0 --";
        
        $I->fillField('input[name="order_no"]', $maliciousInput);
        $I->click('検索');
        
        $I->dontSee('SQL syntax error');
        $I->dontSee('MySQL server has gone away');
        $I->see('受注管理');
    }

    /**
     * SQLインジェクション対策テスト - ログイン画面
     */
    public function sql_injection_ログイン画面(AcceptanceTester $I)
    {
        $I->wantTo('SEC0201-UC01-T05 SQLインジェクション対策 - ログイン画面');
        
        $I->amOnPage('/admin');
        
        $maliciousInputs = [
            "admin' --",
            "admin' OR '1'='1' --",
            "'; DROP TABLE dtb_member; --"
        ];
        
        foreach ($maliciousInputs as $input) {
            $I->fillField('input[name="login_id"]', $input);
            $I->fillField('input[name="password"]', 'password');
            $I->click('ログイン');
            
            $I->dontSee('SQL syntax error');
            $I->dontSee('管理画面');
            $I->see('ログインID、パスワードが正しくありません');
            
            $I->amOnPage('/admin');
        }
    }

    /**
     * SQLインジェクション対策テスト - パラメータ改ざん
     */
    public function sql_injection_パラメータ改ざん(AcceptanceTester $I)
    {
        $I->wantTo('SEC0201-UC01-T06 SQLインジェクション対策 - パラメータ改ざん');
        
        $I->loginAsAdmin();
        
        $maliciousParams = [
            '/admin/product/product/1\' OR 1=1 --/edit',
            '/admin/customer/1\' UNION SELECT password FROM dtb_member --',
            '/admin/order/1\'; DROP TABLE dtb_order; --'
        ];
        
        foreach ($maliciousParams as $url) {
            $I->amOnPage($url);
            
            $I->dontSee('SQL syntax error');
            $I->dontSee('MySQL server has gone away');
            $I->dontSee('password');
            
            $I->seeResponseCodeIsNot(500);
        }
    }
}
