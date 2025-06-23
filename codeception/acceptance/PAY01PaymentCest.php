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
 * @group payment
 * @group integration
 * @group order
 */
class PAY01PaymentCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie('PHPSESSID');
    }

    /**
     * クレジットカード決済統合テスト - 正常処理
     */
    public function payment_クレジットカード決済正常処理(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T01 クレジットカード決済統合テスト - 正常処理');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $I->fillField('email', $faker->safeEmail);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        
        $I->selectOption('Payment', '1'); // クレジットカード決済
        $I->click('次へ');
        
        $I->see('ご注文内容のご確認');
        $I->see('クレジットカード');
        
        $I->fillField('credit_card_number', '4111111111111111');
        $I->fillField('credit_card_expiry', '12/25');
        $I->fillField('credit_card_cvv', '123');
        $I->fillField('credit_card_name', 'TEST USER');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました');
        $I->see('決済が完了しました');
        
        $I->seeInDatabase('dtb_order', [
            'payment_method' => 'クレジットカード',
            'payment_status' => '決済完了'
        ]);
    }

    /**
     * クレジットカード決済統合テスト - エラー処理
     */
    public function payment_クレジットカード決済エラー処理(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T02 クレジットカード決済統合テスト - エラー処理');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $I->fillField('email', $faker->safeEmail);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        
        $I->selectOption('Payment', '1'); // クレジットカード決済
        $I->click('次へ');
        
        $invalidCardNumbers = [
            '4000000000000002', // 決済エラーカード
            '1234567890123456', // 無効なカード番号
            '4111111111111112'  // チェックサムエラー
        ];
        
        foreach ($invalidCardNumbers as $cardNumber) {
            $I->fillField('credit_card_number', $cardNumber);
            $I->fillField('credit_card_expiry', '12/25');
            $I->fillField('credit_card_cvv', '123');
            $I->fillField('credit_card_name', 'TEST USER');
            
            $I->click('注文する');
            
            $I->see('決済エラーが発生しました');
            $I->dontSee('ご注文ありがとうございました');
            
            $I->amOnPage('/shopping');
        }
    }

    /**
     * 銀行振込決済テスト
     */
    public function payment_銀行振込決済(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T03 銀行振込決済テスト');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $I->fillField('email', $faker->safeEmail);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        
        $I->selectOption('Payment', '2'); // 銀行振込
        $I->click('次へ');
        
        $I->see('ご注文内容のご確認');
        $I->see('銀行振込');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました');
        $I->see('振込先口座情報');
        $I->see('入金確認後に商品を発送いたします');
        
        $I->seeInDatabase('dtb_order', [
            'payment_method' => '銀行振込',
            'payment_status' => '入金待ち'
        ]);
    }

    /**
     * 代金引換決済テスト
     */
    public function payment_代金引換決済(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T04 代金引換決済テスト');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $I->fillField('email', $faker->safeEmail);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        
        $I->selectOption('Payment', '3'); // 代金引換
        $I->click('次へ');
        
        $I->see('ご注文内容のご確認');
        $I->see('代金引換');
        $I->see('代引手数料');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました');
        $I->see('商品お届け時にお支払いください');
        
        $I->seeInDatabase('dtb_order', [
            'payment_method' => '代金引換',
            'payment_status' => '未決済'
        ]);
    }

    /**
     * 決済方法変更テスト
     */
    public function payment_決済方法変更(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T05 決済方法変更テスト');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $I->fillField('email', $faker->safeEmail);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        
        $I->selectOption('Payment', '1'); // クレジットカード
        $I->click('次へ');
        $I->see('クレジットカード');
        
        $I->click('戻る');
        
        $I->selectOption('Payment', '2'); // 銀行振込に変更
        $I->click('次へ');
        $I->see('銀行振込');
        
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました');
        
        $I->seeInDatabase('dtb_order', [
            'payment_method' => '銀行振込'
        ]);
    }

    /**
     * 決済タイムアウトテスト
     */
    public function payment_決済タイムアウト(AcceptanceTester $I)
    {
        $I->wantTo('PAY0101-UC01-T06 決済タイムアウトテスト');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $I->fillField('email', $faker->safeEmail);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        
        $I->selectOption('Payment', '1'); // クレジットカード決済
        $I->click('次へ');
        
        $I->fillField('credit_card_number', '4000000000000408'); // タイムアウトテストカード
        $I->fillField('credit_card_expiry', '12/25');
        $I->fillField('credit_card_cvv', '123');
        $I->fillField('credit_card_name', 'TEST USER');
        
        $I->click('注文する');
        
        $I->see('決済処理がタイムアウトしました');
        $I->see('しばらく時間をおいて再度お試しください');
        
        $I->seeInDatabase('dtb_order', [
            'payment_status' => '決済エラー'
        ]);
    }
}
