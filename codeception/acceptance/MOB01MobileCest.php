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
 * @group mobile
 * @group responsive
 * @group ui
 */
class MOB01MobileCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->resizeWindow(375, 667); // iPhone 6/7/8 size
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie('PHPSESSID');
        $I->resizeWindow(1920, 1080); // Reset to desktop size
    }

    /**
     * モバイル画面レスポンシブデザインテスト
     */
    public function mobile_responsive_トップページ表示(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T01 モバイル画面レスポンシブデザインテスト - トップページ');
        
        $I->amOnPage('/');
        
        $I->seeElement('.ec-headerRole');
        $I->seeElement('.ec-sliderRole');
        $I->seeElement('.ec-shelfRole');
        
        $I->seeElement('.ec-headerRole__navSP');
        
        $I->click('.ec-headerRole__navSP .ec-navSP__toggle');
        $I->waitForElementVisible('.ec-navSP__nav', 3);
        $I->seeElement('.ec-navSP__nav');
        
        $I->click('.ec-headerRole__navSP .ec-navSP__toggle');
        $I->waitForElementNotVisible('.ec-navSP__nav', 3);
    }

    /**
     * モバイル商品一覧表示テスト
     */
    public function mobile_responsive_商品一覧表示(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T02 モバイル商品一覧表示テスト');
        
        $I->amOnPage('/products/list');
        
        $I->seeElement('.ec-shelfGrid');
        $I->seeElement('.ec-shelfGrid__item');
        
        $I->seeElement('.ec-shelfRole__filter');
        
        $I->seeElement('.ec-shelfGrid__item .ec-shelfGrid__item-image img');
        
        $I->seeElement('.ec-shelfGrid__item .ec-shelfGrid__item-name');
        $I->seeElement('.ec-shelfGrid__item .ec-shelfGrid__item-price');
        
        $I->seeElement('.ec-pagerRole');
    }

    /**
     * モバイル商品詳細表示テスト
     */
    public function mobile_responsive_商品詳細表示(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T03 モバイル商品詳細表示テスト');
        
        $I->amOnPage('/products/list');
        $I->click('.ec-shelfGrid__item:first-child a');
        
        $I->seeElement('.ec-productRole__img');
        $I->seeElement('.ec-productRole__profile');
        $I->seeElement('.ec-productRole__btn');
        
        $I->seeElement('.ec-productRole__img img');
        
        $I->seeElement('button[type="submit"]');
        
        $I->seeElement('.ec-productRole__description');
    }

    /**
     * モバイルカート機能テスト
     */
    public function mobile_responsive_カート機能(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T04 モバイルカート機能テスト');
        
        $I->amOnPage('/products/list');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        
        $I->amOnPage('/cart');
        $I->seeElement('.ec-cartRole');
        $I->seeElement('.ec-cartRole__progress');
        
        $I->seeElement('.ec-cartRow');
        $I->seeElement('.ec-cartRow__name');
        $I->seeElement('.ec-cartRow__price');
        $I->seeElement('.ec-cartRow__quantity');
        
        $I->seeElement('.ec-cartRow__amountUpDown');
        
        $I->seeElement('.ec-cartRole__btn');
    }

    /**
     * モバイル会員登録テスト
     */
    public function mobile_responsive_会員登録(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T05 モバイル会員登録テスト');
        
        $I->amOnPage('/entry');
        
        $I->seeElement('.ec-registerRole');
        $I->seeElement('input[name="name[name01]"]');
        $I->seeElement('input[name="name[name02]"]');
        $I->seeElement('input[name="email[first]"]');
        $I->seeElement('input[name="password[first]"]');
        
        $faker = Fixtures::get('faker');
        $I->fillField('name[name01]', $faker->lastName);
        $I->fillField('name[name02]', $faker->firstName);
        $I->fillField('kana[kana01]', 'タナカ');
        $I->fillField('kana[kana02]', 'タロウ');
        $I->fillField('postal_code', '1000001');
        
        $I->seeElement('select[name="pref"]');
        $I->selectOption('pref', '13'); // 東京都
        
        $I->seeElement('button[type="submit"]');
    }

    /**
     * モバイルログイン機能テスト
     */
    public function mobile_responsive_ログイン機能(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T06 モバイルログイン機能テスト');
        
        $I->amOnPage('/mypage/login');
        
        $I->seeElement('.ec-loginRole');
        $I->seeElement('input[name="login_email"]');
        $I->seeElement('input[name="login_pass"]');
        
        $I->seeElement('button[type="submit"]');
        
        $I->seeElement('a[href*="forgot"]');
        
        $I->seeElement('a[href*="entry"]');
    }

    /**
     * モバイル検索機能テスト
     */
    public function mobile_responsive_検索機能(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T07 モバイル検索機能テスト');
        
        $I->amOnPage('/');
        
        $I->seeElement('.ec-searchBox');
        $I->seeElement('input[name="name"]');
        
        $I->fillField('name', 'テスト');
        $I->click('.ec-searchBox__btn');
        
        $I->seeElement('.ec-searchResult');
        $I->seeElement('.ec-shelfGrid');
        
        $I->seeElement('.ec-searchResult__condition');
    }

    /**
     * モバイルマイページテスト
     */
    public function mobile_responsive_マイページ(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T08 モバイルマイページテスト');
        
        $I->loginAsMember('zenkoku@example.com', 'password');
        $I->amOnPage('/mypage');
        
        $I->seeElement('.ec-mypageRole');
        $I->seeElement('.ec-mypageRole__nav');
        
        $I->seeElement('a[href*="change"]'); // 会員情報変更
        $I->seeElement('a[href*="history"]'); // 購入履歴
        $I->seeElement('a[href*="favorite"]'); // お気に入り
        
        $I->click('a[href*="history"]');
        $I->seeElement('.ec-historyRole');
    }

    /**
     * モバイルタッチ操作テスト
     */
    public function mobile_responsive_タッチ操作(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T09 モバイルタッチ操作テスト');
        
        $I->amOnPage('/products/list');
        
        $I->click('.ec-shelfGrid__item:first-child');
        $I->seeInCurrentUrl('/products/detail/');
        
        $I->scrollTo('.ec-productRole__description');
        $I->seeElement('.ec-productRole__description');
        
        $I->amOnPage('/entry');
        $I->click('input[name="name[name01]"]');
        $I->seeElement('input[name="name[name01]"]:focus');
    }

    /**
     * モバイル表示速度テスト
     */
    public function mobile_responsive_表示速度(AcceptanceTester $I)
    {
        $I->wantTo('MOB0101-UC01-T10 モバイル表示速度テスト');
        
        $startTime = microtime(true);
        
        $I->amOnPage('/');
        
        $I->seeElement('.ec-headerRole');
        $I->seeElement('.ec-sliderRole');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $I->assertLessThan(3.0, $loadTime, 'ページ読み込み時間が3秒を超えています');
        
        $I->seeElement('img[loading="lazy"]');
    }
}
