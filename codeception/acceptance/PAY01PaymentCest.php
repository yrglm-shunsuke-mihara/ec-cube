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
use Page\Front\ShoppingPage;
use Page\Front\CartPage;

/**
 * @group front
 * @group payment
 * @group integration
 */
class PAY01PaymentCest
{
    public function _before(AcceptanceTester $I)
    {
        $createCustomer = Fixtures::get('createCustomer');
        $this->customer = $createCustomer();
        $I->loginAsMember($this->customer->getEmail(), 'password');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * クレジットカード決済統合テスト
     */
    public function payment_クレジットカード決済(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T01 クレジットカード決済統合テスト');
        
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->checkOption('payment_1'); // クレジットカード決済を想定
        $I->click('次へ');
        
        $I->see('クレジットカード', '.ec-orderRole__summary');
        
        $I->click('注文する');
        
        $I->see('決済処理中', 'h1');
        
        if ($I->seeElement('input[name="card_number"]')) {
            $I->fillField('card_number', '4111111111111111'); // テスト用カード番号
            $I->fillField('expiry_month', '12');
            $I->fillField('expiry_year', '2025');
            $I->fillField('security_code', '123');
            $I->click('決済実行');
        }
        
        $I->see('ご注文ありがとうございました', 'h1');
        $I->see('注文番号', '.ec-orderRole__summary');
        
        $I->seeInDatabase('dtb_order', [
            'Customer' => $this->customer->getId(),
            'OrderStatus' => 1 // 新規受付
        ]);
    }

    /**
     * 銀行振込決済テスト
     */
    public function payment_銀行振込(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T02 銀行振込決済テスト');
        
        $I->amOnPage('/products/detail/2');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->checkOption('payment_2'); // 銀行振込を想定
        $I->click('次へ');
        
        $I->see('銀行振込', '.ec-orderRole__summary');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました', 'h1');
        $I->see('お振込先', '.ec-orderRole__summary');
        $I->see('銀行名', '.ec-orderRole__summary');
        $I->see('支店名', '.ec-orderRole__summary');
        $I->see('口座番号', '.ec-orderRole__summary');
        
        $I->seeInDatabase('dtb_order', [
            'Customer' => $this->customer->getId(),
            'OrderStatus' => 2 // 入金待ち
        ]);
    }

    /**
     * 代金引換決済テスト
     */
    public function payment_代金引換(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T03 代金引換決済テスト');
        
        $I->amOnPage('/products/detail/3');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->checkOption('payment_3'); // 代金引換を想定
        $I->click('次へ');
        
        $I->see('代金引換', '.ec-orderRole__summary');
        $I->see('代引手数料', '.ec-orderRole__summary');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました', 'h1');
        $I->see('商品お届け時にお支払いください', '.ec-orderRole__summary');
        
        $I->seeInDatabase('dtb_order', [
            'Customer' => $this->customer->getId(),
            'OrderStatus' => 1 // 新規受付
        ]);
    }

    /**
     * 決済方法変更テスト
     */
    public function payment_決済方法変更(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T04 決済方法変更テスト');
        
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->checkOption('payment_1');
        $I->click('次へ');
        
        $I->see('クレジットカード', '.ec-orderRole__summary');
        
        $I->click('戻る');
        
        $I->checkOption('payment_2');
        $I->click('次へ');
        
        $I->see('銀行振込', '.ec-orderRole__summary');
        $I->dontSee('クレジットカード', '.ec-orderRole__summary');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました', 'h1');
    }

    /**
     * 決済エラーハンドリングテスト
     */
    public function payment_決済エラーハンドリング(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T05 決済エラーハンドリングテスト');
        
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->checkOption('payment_1');
        $I->click('次へ');
        
        $I->click('注文する');
        
        if ($I->seeElement('input[name="card_number"]')) {
            $I->fillField('card_number', '4000000000000002'); // テスト用エラーカード
            $I->fillField('expiry_month', '12');
            $I->fillField('expiry_year', '2025');
            $I->fillField('security_code', '123');
            $I->click('決済実行');
            
            $I->see('決済処理でエラーが発生しました', '.ec-errorMessage');
            
            $I->dontSeeInDatabase('dtb_order', [
                'Customer' => $this->customer->getId(),
                'OrderStatus' => 5 // 決済処理中
            ]);
        }
    }

    /**
     * 複数商品での決済テスト
     */
    public function payment_複数商品決済(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T06 複数商品での決済テスト');
        
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/products/detail/2');
        $I->selectOption('select[name="quantity"]', '2');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->see('商品合計：3点');
        
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->checkOption('payment_2');
        $I->click('次へ');
        
        $I->see('商品合計：3点', '.ec-orderRole__summary');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました', 'h1');
        
        $orderId = $I->grabFromDatabase('dtb_order', 'id', [
            'Customer' => $this->customer->getId()
        ]);
        
        $I->seeInDatabase('dtb_order_item', [
            'Order' => $orderId,
            'quantity' => 1
        ]);
        
        $I->seeInDatabase('dtb_order_item', [
            'Order' => $orderId,
            'quantity' => 2
        ]);
    }
}
