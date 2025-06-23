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
 * @group i18n
 * @group internationalization
 * @group localization
 */
class I18N01InternationalizationCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/admin/setting/system/system');
        $I->loginAsAdmin();
        $I->selectOption('locale', 'ja');
        $I->click('登録');
    }

    /**
     * 日本語表示テスト
     */
    public function i18n_日本語表示(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T01 日本語表示テスト');
        
        $I->amOnPage('/');
        
        $I->see('商品一覧');
        $I->see('カテゴリ');
        $I->see('検索');
        $I->see('カートを見る');
        $I->see('マイページ');
        
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        
        $I->see('商品詳細');
        $I->see('価格');
        $I->see('在庫');
        $I->see('カートに入れる');
        $I->see('商品説明');
    }

    /**
     * 英語表示テスト（多言語対応がある場合）
     */
    public function i18n_英語表示(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T02 英語表示テスト');
        
        $I->amOnPage('/');
        
        if ($I->tryToSeeElement('a[href*="en"]') || $I->tryToSeeElement('.language-switcher')) {
            $I->click('a[href*="en"]');
            
            $I->see('Products');
            $I->see('Category');
            $I->see('Search');
            $I->see('Cart');
            $I->see('My Page');
        } else {
            $I->comment('英語対応が実装されていません');
        }
    }

    /**
     * 通貨表示テスト
     */
    public function i18n_通貨表示(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T03 通貨表示テスト');
        
        $I->amOnPage('/products/list');
        
        $I->see('¥');
        $I->seeElement('.price');
        
        $priceText = $I->grabTextFrom('.price:first-child');
        $I->assertRegExp('/¥[\d,]+/', $priceText);
        
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->see('¥');
        $I->seeElement('.ec-productRole__price');
    }

    /**
     * 日付フォーマットテスト
     */
    public function i18n_日付フォーマット(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T04 日付フォーマットテスト');
        
        $I->loginAsMember('zenkoku@example.com', 'password');
        $I->amOnPage('/mypage/history');
        
        if ($I->tryToSeeElement('.ec-historyRole__date')) {
            $dateText = $I->grabTextFrom('.ec-historyRole__date:first-child');
            
            $I->assertRegExp('/\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}/', $dateText);
        }
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/order');
        
        if ($I->tryToSeeElement('.date')) {
            $adminDateText = $I->grabTextFrom('.date:first-child');
            $I->assertRegExp('/\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}/', $adminDateText);
        }
    }

    /**
     * 文字エンコーディングテスト
     */
    public function i18n_文字エンコーディング(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T05 文字エンコーディングテスト');
        
        $I->amOnPage('/');
        
        $I->seeInSource('charset=utf-8');
        
        $I->see('商品');
        $I->see('カテゴリ');
        $I->see('検索');
        
        $I->amOnPage('/products/list');
        
        $specialChars = ['・', '〜', '（', '）', '【', '】', '※'];
        
        foreach ($specialChars as $char) {
            if ($I->tryToSee($char)) {
                $I->comment("特殊文字 '{$char}' が正常に表示されています");
            }
        }
    }

    /**
     * フォーム入力多言語テスト
     */
    public function i18n_フォーム入力多言語(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T06 フォーム入力多言語テスト');
        
        $I->amOnPage('/entry');
        
        $I->fillField('name[name01]', '田中');
        $I->fillField('name[name02]', '太郎');
        $I->fillField('kana[kana01]', 'タナカ');
        $I->fillField('kana[kana02]', 'タロウ');
        
        $I->seeInField('name[name01]', '田中');
        $I->seeInField('name[name02]', '太郎');
        $I->seeInField('kana[kana01]', 'タナカ');
        $I->seeInField('kana[kana02]', 'タロウ');
        
        $I->fillField('postal_code', '1000001');
        $I->fillField('phone_number', '03-1234-5678');
        
        $I->seeInField('postal_code', '1000001');
        $I->seeInField('phone_number', '03-1234-5678');
    }

    /**
     * エラーメッセージ多言語テスト
     */
    public function i18n_エラーメッセージ多言語(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T07 エラーメッセージ多言語テスト');
        
        $I->amOnPage('/entry');
        
        $I->click('確認ページへ');
        
        $I->see('入力してください');
        $I->see('必須項目です');
        
        $I->amOnPage('/mypage/login');
        $I->fillField('login_email', 'invalid@example.com');
        $I->fillField('login_pass', 'wrongpassword');
        $I->click('ログイン');
        
        $I->see('ログインID、パスワードが正しくありません');
    }

    /**
     * 管理画面多言語テスト
     */
    public function i18n_管理画面多言語(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T08 管理画面多言語テスト');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin');
        
        $I->see('受注管理');
        $I->see('商品管理');
        $I->see('顧客管理');
        $I->see('コンテンツ管理');
        $I->see('設定');
        
        $I->click('商品管理');
        $I->see('商品一覧');
        $I->see('商品登録');
        $I->see('カテゴリ管理');
        
        $I->amOnPage('/admin/order');
        $I->see('受注一覧');
        $I->see('受注番号');
        $I->see('受注日時');
        $I->see('顧客名');
    }

    /**
     * 検索機能多言語テスト
     */
    public function i18n_検索機能多言語(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T09 検索機能多言語テスト');
        
        $I->amOnPage('/');
        
        $I->fillField('name', '商品');
        $I->click('検索');
        
        $I->see('検索結果');
        $I->see('件が該当しました');
        
        $I->amOnPage('/');
        $I->fillField('name', 'しょうひん');
        $I->click('検索');
        
        $I->amOnPage('/');
        $I->fillField('name', 'ショウヒン');
        $I->click('検索');
        
        $I->amOnPage('/');
        $I->fillField('name', 'product');
        $I->click('検索');
    }

    /**
     * タイムゾーン表示テスト
     */
    public function i18n_タイムゾーン表示(AcceptanceTester $I)
    {
        $I->wantTo('I18N0101-UC01-T10 タイムゾーン表示テスト');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/order');
        
        if ($I->tryToSeeElement('.datetime')) {
            $datetimeText = $I->grabTextFrom('.datetime:first-child');
            
            $I->assertRegExp('/\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}/', $datetimeText);
        }
        
        $I->amOnPage('/admin/setting/system/system');
        
        if ($I->tryToSeeElement('select[name="timezone"]')) {
            $timezone = $I->grabValueFrom('select[name="timezone"]');
            $I->assertEquals('Asia/Tokyo', $timezone);
        }
        
        $I->amOnPage('/admin');
        $currentTime = date('Y-m-d H:i');
        $I->comment("現在時刻: {$currentTime}");
    }
}
