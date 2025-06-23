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
 * @group data
 * @group integrity
 * @group database
 */
class DATA01IntegrityCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie('PHPSESSID');
    }

    /**
     * 商品データ整合性テスト
     */
    public function data_integrity_商品データ整合性(AcceptanceTester $I)
    {
        $I->wantTo('DATA0101-UC01-T01 商品データ整合性テスト');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/product/product/new');
        
        $faker = Fixtures::get('faker');
        $productName = $faker->words(3, true);
        
        $I->fillField('name', $productName);
        $I->fillField('description_detail', $faker->paragraph);
        $I->fillField('price02', '1000');
        $I->fillField('stock', '10');
        $I->selectOption('Status', '1'); // 公開
        $I->selectOption('Category', '1');
        
        $I->click('登録');
        
        $I->see('保存しました');
        
        $I->seeInDatabase('dtb_product', [
            'name' => $productName,
            'price02' => 1000,
            'stock' => 10,
            'Status' => 1
        ]);
        
        $productId = $I->grabFromDatabase('dtb_product', 'id', ['name' => $productName]);
        
        $I->seeInDatabase('dtb_product_category', [
            'product_id' => $productId,
            'category_id' => 1
        ]);
        
        $I->amOnPage('/products/list');
        $I->see($productName);
    }

    /**
     * 顧客データ整合性テスト
     */
    public function data_integrity_顧客データ整合性(AcceptanceTester $I)
    {
        $I->wantTo('DATA0101-UC01-T02 顧客データ整合性テスト');
        
        $faker = Fixtures::get('faker');
        $email = $faker->safeEmail;
        
        $I->amOnPage('/entry');
        
        $I->fillField('name[name01]', $faker->lastName);
        $I->fillField('name[name02]', $faker->firstName);
        $I->fillField('kana[kana01]', 'タナカ');
        $I->fillField('kana[kana02]', 'タロウ');
        $I->fillField('postal_code', '1000001');
        $I->selectOption('pref', '13'); // 東京都
        $I->fillField('addr01', '千代田区');
        $I->fillField('addr02', '1-1-1');
        $I->fillField('phone_number', '03-1234-5678');
        $I->fillField('email[first]', $email);
        $I->fillField('email[second]', $email);
        $I->fillField('password[first]', 'password123');
        $I->fillField('password[second]', 'password123');
        $I->checkOption('agree_terms');
        
        $I->click('確認ページへ');
        $I->click('会員登録をする');
        
        $I->see('会員登録が完了しました');
        
        $I->seeInDatabase('dtb_customer', [
            'email' => $email,
            'name01' => $faker->lastName,
            'name02' => $faker->firstName
        ]);
        
        $customerId = $I->grabFromDatabase('dtb_customer', 'id', ['email' => $email]);
        
        $I->seeInDatabase('dtb_customer_address', [
            'customer_id' => $customerId,
            'postal_code' => '1000001',
            'pref' => 13
        ]);
    }

    /**
     * 注文データ整合性テスト
     */
    public function data_integrity_注文データ整合性(AcceptanceTester $I)
    {
        $I->wantTo('DATA0101-UC01-T03 注文データ整合性テスト');
        
        $faker = Fixtures::get('faker');
        
        $I->amOnPage('/');
        $I->click('商品一覧');
        $I->click('.ec-shelfGrid__item:first-child a');
        $I->click('カートに入れる');
        $I->click('レジに進む');
        
        $email = $faker->safeEmail;
        $I->fillField('email', $email);
        $I->fillField('password', 'password123');
        $I->click('ログイン');
        
        $I->click('ご注文手続きへ');
        $I->selectOption('Payment', '2'); // 銀行振込
        $I->click('次へ');
        $I->click('注文する');
        
        $I->see('ご注文ありがとうございました');
        
        $orderId = $I->grabFromDatabase('dtb_order', 'id', ['order_email' => $email]);
        
        $I->seeInDatabase('dtb_order', [
            'id' => $orderId,
            'order_email' => $email,
            'payment_method' => '銀行振込'
        ]);
        
        $I->seeInDatabase('dtb_order_item', [
            'order_id' => $orderId
        ]);
        
        $productId = $I->grabFromDatabase('dtb_order_item', 'product_id', ['order_id' => $orderId]);
        $I->seeInDatabase('dtb_product_stock', [
            'product_id' => $productId
        ]);
    }

    /**
     * カテゴリデータ整合性テスト
     */
    public function data_integrity_カテゴリデータ整合性(AcceptanceTester $I)
    {
        $I->wantTo('DATA0101-UC01-T04 カテゴリデータ整合性テスト');
        
        $I->loginAsAdmin();
        $I->amOnPage('/admin/product/category');
        
        $faker = Fixtures::get('faker');
        $categoryName = $faker->words(2, true);
        
        $I->fillField('name', $categoryName);
        $I->fillField('sort_no', '100');
        $I->click('新規入力');
        
        $I->see('保存しました');
        
        $I->seeInDatabase('dtb_category', [
            'name' => $categoryName,
            'sort_no' => 100
        ]);
        
        $categoryId = $I->grabFromDatabase('dtb_category', 'id', ['name' => $categoryName]);
        
        $I->seeInDatabase('dtb_category', [
            'id' => $categoryId,
            'level' => 1 // ルートレベル
        ]);
        
        $I->amOnPage('/');
        $I->see($categoryName);
    }

    /**
     * 在庫データ整合性テスト
     */
    public function data_integrity_在庫データ整合性(AcceptanceTester $I)
    {
        $I->wantTo('DATA0101-UC01-T05 在庫データ整合性テスト');
        
        $I->loginAsAdmin();
        
        $productId = $I->grabFromDatabase('dtb_product', 'id', [], 'ORDER BY id LIMIT 1');
        $initialStock = $I->grabFromDatabase('dtb_product', 'stock', ['id' => $productId]);
        
        $I->amOnPage("/admin/product/product/{$productId}/edit");
        
        $newStock = $initialStock + 10;
        $I->fillField('stock', $newStock);
        $I->click('登録');
        
        $I->see('保存しました');
        
        $I->seeInDatabase('dtb_product', [
            'id' => $productId,
            'stock' => $newStock
        ]);
        
        $I->seeInDatabase('dtb_product_stock', [
            'product_id' => $productId
        ]);
    }

    /**
     * 外部キー制約整合性テスト
     */
    public function data_integrity_外部キー制約整合性(AcceptanceTester $I)
    {
        $I->wantTo('DATA0101-UC01-T06 外部キー制約整合性テスト');
        
        $productId = $I->grabFromDatabase('dtb_product', 'id', [], 'ORDER BY id LIMIT 1');
        $categoryId = $I->grabFromDatabase('dtb_product_category', 'category_id', ['product_id' => $productId]);
        
        $I->seeInDatabase('dtb_category', [
            'id' => $categoryId
        ]);
        
        $orderId = $I->grabFromDatabase('dtb_order', 'id', [], 'ORDER BY id LIMIT 1');
        if ($orderId) {
            $customerId = $I->grabFromDatabase('dtb_order', 'customer_id', ['id' => $orderId]);
            
            if ($customerId) {
                $I->seeInDatabase('dtb_customer', [
                    'id' => $customerId
                ]);
            }
        }
        
        $orderItemId = $I->grabFromDatabase('dtb_order_item', 'id', [], 'ORDER BY id LIMIT 1');
        if ($orderItemId) {
            $productId = $I->grabFromDatabase('dtb_order_item', 'product_id', ['id' => $orderItemId]);
            
            $I->seeInDatabase('dtb_product', [
                'id' => $productId
            ]);
        }
    }
}
