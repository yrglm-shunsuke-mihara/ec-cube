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
     * 正常なCSRFトークンでの処理確認テスト
     */
    public function csrf_正常トークン確認(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T01 正常なCSRFトークンでの処理確認');
        
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

    /**
     * CSRFトークンの存在確認テスト
     */
    public function csrf_トークン存在確認(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T02 CSRFトークンの存在確認');
        
        $createProduct = Fixtures::get('createProduct');
        $product = $createProduct('CSRFトークン確認商品', 1);
        
        $config = Fixtures::get('config');
        $I->amOnPage('/'.$config['eccube_admin_route'].'/product/'.$product->getId().'/edit');
        
        $I->see('商品登録・編集', 'h1');
        
        $I->seeElement('input[name*="_token"]');
        
        $tokenValue = $I->grabValueFrom('input[name*="_token"]');
        $I->assertNotEmpty($tokenValue, 'CSRFトークンが空です');
        $I->assertGreaterThan(10, strlen($tokenValue), 'CSRFトークンが短すぎます');
    }

    /**
     * フロント画面でのCSRFトークン存在確認
     */
    public function csrf_フロントトークン確認(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T03 フロント画面でのCSRFトークン存在確認');
        
        $I->logoutAsAdmin();
        
        $I->amOnPage('/entry');
        $I->see('会員登録', 'h1');
        
        $I->seeElement('input[name*="_token"]');
        
        $tokenValue = $I->grabValueFrom('input[name*="_token"]');
        $I->assertNotEmpty($tokenValue, 'フロントCSRFトークンが空です');
        $I->assertGreaterThan(10, strlen($tokenValue), 'フロントCSRFトークンが短すぎます');
    }
}
