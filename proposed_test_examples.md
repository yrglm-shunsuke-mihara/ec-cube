# 提案テストケースの実装例

このドキュメントでは、提案した新しいテストケースの具体的な実装例を示します。

## 1. セキュリティテスト - CSRF攻撃対策

### SEC01CSRFCest.php

```php
<?php

use Codeception\Util\Fixtures;
use Page\Admin\ProductManagePage;

/**
 * @group admin
 * @group security
 * @group csrf
 */
class SEC01CSRFCest
{
    public function _before(AcceptanceTester $I)
    {
        // 管理者としてログイン
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
        
        // 商品編集ページにアクセス
        $I->amOnPage('/admin/product/product/1/edit');
        
        // CSRFトークンを取得
        $csrfToken = $I->grabValueFrom('input[name="_token"]');
        
        // 無効なCSRFトークンで商品更新を試行
        $I->sendPOST('/admin/product/product/1/edit', [
            '_token' => 'invalid_token',
            'name' => 'ハッキング商品名',
            'description_detail' => 'ハッキングされた商品説明'
        ]);
        
        // CSRF攻撃が防がれることを確認
        $I->seeResponseCodeIs(403);
        
        // 正しいCSRFトークンでは更新できることを確認
        $I->sendPOST('/admin/product/product/1/edit', [
            '_token' => $csrfToken,
            'name' => '正常な商品名',
            'description_detail' => '正常な商品説明'
        ]);
        
        $I->seeResponseCodeIs(302); // リダイレクト成功
    }

    /**
     * CSRF攻撃対策テスト - フロント会員登録
     */
    public function csrf_フロント会員登録(AcceptanceTester $I)
    {
        $I->wantTo('SEC0101-UC01-T02 CSRF攻撃対策 - フロント会員登録');
        
        $faker = Fixtures::get('faker');
        
        // 会員登録ページにアクセス
        $I->amOnPage('/entry');
        
        // 無効なCSRFトークンで会員登録を試行
        $I->sendPOST('/entry', [
            '_token' => 'invalid_token',
            'name' => [
                'name01' => $faker->lastName,
                'name02' => $faker->firstName
            ],
            'email' => [
                'first' => $faker->safeEmail,
                'second' => $faker->safeEmail
            ],
            'password' => [
                'first' => 'password123',
                'second' => 'password123'
            ],
            'agree_terms' => 1
        ]);
        
        // CSRF攻撃が防がれることを確認
        $I->seeResponseCodeIs(403);
    }
}
```

## 2. 在庫管理エッジケーステスト

### INV01StockCest.php

```php
<?php

use Codeception\Util\Fixtures;
use Page\Front\ProductDetailPage;
use Page\Front\CartPage;

/**
 * @group front
 * @group inventory
 * @group stock
 */
class INV01StockCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * 在庫切れ商品の購入試行テスト
     */
    public function stock_在庫切れ商品購入(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T01 在庫切れ商品の購入試行');
        
        // テスト用商品の在庫を0に設定
        $entityManager = Fixtures::get('entityManager');
        $product = $entityManager->getRepository('Eccube\Entity\Product')->find(1);
        $productClass = $product->getProductClasses()->first();
        $productClass->setStock(0);
        $entityManager->flush();
        
        // 商品詳細ページにアクセス
        ProductDetailPage::go($I, 1);
        
        // カートに追加ボタンが無効化されていることを確認
        $I->dontSee('カートに入れる', '.ec-productRole__btn');
        $I->see('在庫切れ', '.ec-productRole__btn');
        
        // 直接POSTリクエストでカート追加を試行
        $I->sendPOST('/products/add_cart/1', [
            'product_id' => 1,
            'quantity' => 1
        ]);
        
        // エラーレスポンスを確認
        $I->seeResponseCodeIs(400);
        
        // カートページで商品が追加されていないことを確認
        CartPage::go($I);
        $I->see('現在カート内に商品はございません。');
    }

    /**
     * 在庫数を超える購入試行テスト
     */
    public function stock_在庫数超過購入(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T02 在庫数を超える購入試行');
        
        // テスト用商品の在庫を5に設定
        $entityManager = Fixtures::get('entityManager');
        $product = $entityManager->getRepository('Eccube\Entity\Product')->find(2);
        $productClass = $product->getProductClasses()->first();
        $productClass->setStock(5);
        $entityManager->flush();
        
        // 商品詳細ページにアクセス
        ProductDetailPage::go($I, 2);
        
        // 在庫数を超える数量でカート追加を試行
        $I->selectOption('select[name="quantity"]', '10');
        $I->click('.ec-productRole__btn');
        
        // エラーメッセージの表示確認
        $I->see('在庫数が不足しています', '.ec-errorMessage');
        
        // 正常な数量では追加できることを確認
        $I->selectOption('select[name="quantity"]', '3');
        $I->click('.ec-productRole__btn');
        $I->see('カートに追加しました', '.ec-modal');
    }

    /**
     * 同時購入競合テスト（シミュレーション）
     */
    public function stock_同時購入競合(AcceptanceTester $I)
    {
        $I->wantTo('INV0101-UC01-T03 在庫競合時の処理');
        
        // テスト用商品の在庫を1に設定
        $entityManager = Fixtures::get('entityManager');
        $product = $entityManager->getRepository('Eccube\Entity\Product')->find(3);
        $productClass = $product->getProductClasses()->first();
        $productClass->setStock(1);
        $entityManager->flush();
        
        // 最初のユーザーがカートに追加
        ProductDetailPage::go($I, 3);
        $I->click('.ec-productRole__btn');
        
        // 2番目のユーザー（別セッション）での購入試行をシミュレート
        // 実際の実装では、別のブラウザセッションまたはAPIクライアントを使用
        $I->sendPOST('/products/add_cart/3', [
            'product_id' => 3,
            'quantity' => 1
        ], [], ['HTTP_USER_AGENT' => 'TestUser2']);
        
        // 在庫不足エラーが返されることを確認
        $I->seeResponseCodeIs(400);
    }
}
```

## 3. メール送信機能テスト

### MAIL01NotificationCest.php

```php
<?php

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
        // メールキューをクリア
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
        
        // 会員登録を実行
        EntryPage::go($I)
            ->新規会員登録($email, 'password123', '田中', '太郎');
        
        // メール送信処理を実行
        $I->runShellCommand('php bin/console messenger:consume email --limit=1 --time-limit=10');
        
        // メールログを確認
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => '会員登録のご確認',
            'send_date' => new \DateTime('now', new \DateTimeZone('Asia/Tokyo'))
        ]);
        
        // メール内容の確認（実際の実装では、テスト用メールサーバーまたはファイル出力を使用）
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
        
        // ログインして商品を購入
        $I->loginAsMember($customer->getEmail(), 'password');
        
        // 商品をカートに追加
        $I->amOnPage('/products/detail/1');
        $I->click('.ec-productRole__btn');
        
        // 注文手続きを完了
        ShoppingPage::go($I)
            ->確認する()
            ->注文する();
        
        // メール送信処理を実行
        $I->runShellCommand('php bin/console messenger:consume email --limit=2 --time-limit=10');
        
        // 注文完了メールの送信確認
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => 'ご注文ありがとうございます'
        ]);
        
        // メール内容の確認
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
        
        // パスワード再設定リクエスト
        $I->amOnPage('/forgot');
        $I->fillField('login_email', $customer->getEmail());
        $I->click('次のページへ');
        
        // メール送信処理を実行
        $I->runShellCommand('php bin/console messenger:consume email --limit=1 --time-limit=10');
        
        // パスワード再設定メールの送信確認
        $I->seeInDatabase('dtb_mail_history', [
            'mail_subject' => 'パスワード変更のご確認'
        ]);
        
        // メール内容の確認
        $mailContent = $I->grabFromDatabase('dtb_mail_history', 'mail_body', [
            'mail_subject' => 'パスワード変更のご確認'
        ]);
        
        $I->assertStringContainsString($customer->getEmail(), $mailContent);
        $I->assertStringContainsString('パスワード変更URL', $mailContent);
        $I->assertStringContainsString('有効期限', $mailContent);
    }
}
```

## 実装時の注意点

### 1. テスト環境の設定
- メール送信テストでは、実際のメール送信を避けるためテスト用の設定が必要
- データベースの状態をテスト前後で適切にリセット
- 外部サービス（決済など）はモックまたはテスト環境を使用

### 2. テストデータの管理
- Fixturesを活用してテストデータを効率的に管理
- テスト間でのデータ競合を避けるため、一意性を保つ
- テスト後のクリーンアップを確実に実行

### 3. パフォーマンス考慮
- 大量データを扱うテストは実行時間に注意
- 必要に応じてテストグループを分けて実行
- CI/CDパイプラインでの実行時間を考慮

### 4. セキュリティテストの実装
- 実際の攻撃コードは使用せず、安全な範囲でテスト
- テスト環境でのみ実行し、本番環境では無効化
- セキュリティ専門家のレビューを受ける

これらの実装例を参考に、EC-CUBEの品質向上に貢献する包括的なテストスイートを構築できます。
