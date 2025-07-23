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
use Page\Front\EntryPage;
use Page\Front\ShoppingPage;

/**
 * @group front
 * @group mail
 * @group notification
 */
class MAIL01NotificationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->runShellCommand('php bin/console messenger:consume email --limit=100 --time-limit=1');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * 会員登録完了メール送信テスト
     */
    public function mail_会員登録完了メール(AcceptanceTester $I)
    {
        $I->wantTo('MAIL0101-UC01-T01 会員登録完了メール送信');
        
        $faker = Fixtures::get('faker');
        $email = $faker->safeEmail;
        
        $I->amOnPage('/entry');
        
        $I->fillField('entry[name][name01]', '田中');
        $I->fillField('entry[name][name02]', '太郎');
        $I->fillField('entry[kana][kana01]', 'タナカ');
        $I->fillField('entry[kana][kana02]', 'タロウ');
        $I->fillField('entry[email][first]', $email);
        $I->fillField('entry[email][second]', $email);
        $I->fillField('entry[password][first]', 'password123');
        $I->fillField('entry[password][second]', 'password123');
        $I->selectOption('entry[sex]', '1');
        $I->selectOption('entry[birth][year]', '1990');
        $I->selectOption('entry[birth][month]', '1');
        $I->selectOption('entry[birth][day]', '1');
        $I->fillField('entry[postal_code]', '100-0001');
        $I->selectOption('entry[pref]', '13');
        $I->fillField('entry[addr01]', '千代田区');
        $I->fillField('entry[addr02]', '1-1-1');
        $I->fillField('entry[phone_number]', '03-1234-5678');
        $I->checkOption('entry[user_policy_check]');
        
        $I->click('確認ページへ');
        
        $I->click('会員登録をする');
        
        $I->runShellCommand('php bin/console messenger:consume email --limit=1 --time-limit=10');
        
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => '会員登録のご確認'
        ]);
        
        $mailContent = $I->grabFromDatabase('dtb_mail_history', 'mail_body', [
            'mail_subject' => '会員登録のご確認'
        ]);
        
        $I->assertStringContainsString($email, $mailContent);
        $I->assertStringContainsString('田中 太郎', $mailContent);
        $I->assertStringContainsString('会員登録が完了いたしました', $mailContent);
    }

    /**
     * 注文完了メール送信テスト
     */
    public function mail_注文完了メール(AcceptanceTester $I)
    {
        $I->wantTo('MAIL0101-UC01-T02 注文完了メール送信');
        
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        
        $I->loginAsMember($customer->getEmail(), 'password');
        
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        
        $I->click('次へ');
        
        $I->click('次へ');
        
        $I->click('注文する');
        
        $I->runShellCommand('php bin/console messenger:consume email --limit=2 --time-limit=10');
        
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => 'ご注文ありがとうございます'
        ]);
        
        $mailContent = $I->grabFromDatabase('dtb_mail_history', 'mail_body', [
            'mail_subject' => 'ご注文ありがとうございます'
        ]);
        
        $I->assertStringContainsString($customer->getEmail(), $mailContent);
        $I->assertStringContainsString('ご注文内容', $mailContent);
        $I->assertStringContainsString('お支払い方法', $mailContent);
        $I->assertStringContainsString('配送先', $mailContent);
    }

    /**
     * パスワード再設定メール送信テスト
     */
    public function mail_パスワード再設定メール(AcceptanceTester $I)
    {
        $I->wantTo('MAIL0101-UC01-T03 パスワード再設定メール送信');
        
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        
        $I->amOnPage('/forgot');
        $I->fillField('login_email', $customer->getEmail());
        $I->click('次のページへ');
        
        $I->runShellCommand('php bin/console messenger:consume email --limit=1 --time-limit=10');
        
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => 'パスワード変更のご確認'
        ]);
        
        $mailContent = $I->grabFromDatabase('dtb_mail_history', 'mail_body', [
            'mail_subject' => 'パスワード変更のご確認'
        ]);
        
        $I->assertStringContainsString($customer->getEmail(), $mailContent);
        $I->assertStringContainsString('パスワード変更URL', $mailContent);
        $I->assertStringContainsString('有効期限', $mailContent);
    }

    /**
     * 管理者向け注文通知メール送信テスト
     */
    public function mail_管理者向け注文通知メール(AcceptanceTester $I)
    {
        $I->wantTo('MAIL0101-UC01-T04 管理者向け注文通知メール送信');
        
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        
        $I->loginAsMember($customer->getEmail(), 'password');
        
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        $I->amOnPage('/cart');
        $I->click('レジに進む');
        $I->click('次へ');
        $I->click('次へ');
        $I->click('注文する');
        
        $I->runShellCommand('php bin/console messenger:consume email --limit=3 --time-limit=10');
        
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => '新規受注のお知らせ'
        ]);
        
        $mailContent = $I->grabFromDatabase('dtb_mail_history', 'mail_body', [
            'mail_subject' => '新規受注のお知らせ'
        ]);
        
        $I->assertStringContainsString('新規受注', $mailContent);
        $I->assertStringContainsString($customer->getEmail(), $mailContent);
        $I->assertStringContainsString('受注番号', $mailContent);
    }

    /**
     * 商品問い合わせメール送信テスト
     */
    public function mail_商品問い合わせメール(AcceptanceTester $I)
    {
        $I->wantTo('MAIL0101-UC01-T05 商品問い合わせメール送信');
        
        $faker = Fixtures::get('faker');
        $email = $faker->safeEmail;
        
        $I->amOnPage('/contact');
        
        $I->fillField('contact[name][name01]', '山田');
        $I->fillField('contact[name][name02]', '花子');
        $I->fillField('contact[kana][kana01]', 'ヤマダ');
        $I->fillField('contact[kana][kana02]', 'ハナコ');
        $I->fillField('contact[email]', $email);
        $I->fillField('contact[phone_number]', '03-9876-5432');
        $I->fillField('contact[contents]', '商品について質問があります。');
        
        $I->click('確認ページへ');
        
        $I->click('送信する');
        
        $I->runShellCommand('php bin/console messenger:consume email --limit=2 --time-limit=10');
        
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => 'お問い合わせを受け付けました'
        ]);
        
        $mailContent = $I->grabFromDatabase('dtb_mail_history', 'mail_body', [
            'mail_subject' => 'お問い合わせを受け付けました'
        ]);
        
        $I->assertStringContainsString($email, $mailContent);
        $I->assertStringContainsString('山田 花子', $mailContent);
        $I->assertStringContainsString('商品について質問があります。', $mailContent);
    }
}
