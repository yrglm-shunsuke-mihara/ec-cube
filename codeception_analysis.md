# EC-CUBE Codeception E2Eテストケース分析レポート

## 概要

このドキュメントは、EC-CUBEのcodeceptionディレクトリ以下に実装されているE2Eテストケースの包括的な調査結果と、未実装のテストケースの提案をまとめたものです。

## 現在実装されているテストケース

### 管理画面テスト (EA系)

#### EA01 - 管理画面TOPページテスト
- **EA01TopCest.php**: 管理画面のTOPページ表示、受注状況確認、売上状況表示
- 主要テストシナリオ:
  - 管理画面TOPページの初期表示確認
  - 受注状況の表示確認
  - 売上状況の表示確認

#### EA02 - 認証・ログインテスト
- **EA02AuthenticationCest.php**: 管理画面の認証機能
- 主要テストシナリオ:
  - 管理者ログイン機能
  - ログイン失敗時の処理
  - セッション管理

#### EA03 - 商品管理テスト
- **EA03ProductCest.php**: 商品管理機能の包括的テスト
- 主要テストシナリオ:
  - 商品編集機能
  - 商品CSV登録機能
  - カテゴリ管理機能
  - 規格管理機能
  - 商品一覧表示・検索機能

#### EA04 - 受注管理テスト
- **EA04OrderCest.php**: 受注管理機能
- 主要テストシナリオ:
  - 受注一覧表示
  - 受注詳細確認
  - 受注ステータス変更
  - 受注編集機能

#### EA05 - 顧客管理テスト
- **EA05CustomerCest.php**: 顧客管理機能
- 主要テストシナリオ:
  - 顧客検索機能
  - 顧客編集機能
  - 顧客一覧表示

#### EA06 - コンテンツ管理テスト
- **EA06ContentsManagementCest.php**: コンテンツ管理機能
- 主要テストシナリオ:
  - ページ管理機能
  - ブロック管理機能
  - レイアウト管理機能

#### EA07 - 基本設定テスト
- **EA07BasicinfoCest.php**: 基本設定機能
- 主要テストシナリオ:
  - EA0701-UC01-T01: 基本設定
  - EA0701-UC01-T05/T06: 会員設定（仮会員機能）
  - EA0701-UC01-T07/T08: マイページ注文状況表示設定
  - EA0701-UC01-T09/T10: お気に入り商品機能設定
  - EA0701-UC01-T011/T12: 自動ログイン機能設定

#### EA08 - システム情報テスト
- **EA08SysteminfoCest.php**: システム情報表示
- 主要テストシナリオ:
  - システム情報表示機能
  - PHP情報確認機能

#### EA09 - 配送設定テスト
- **EA09ShippingCest.php**: 配送設定機能
- 主要テストシナリオ:
  - 配送方法設定
  - 配送料金設定

#### EA10 - プラグイン管理テスト
- **EA10PluginCest.php**: プラグイン管理機能の包括的テスト
- 主要テストシナリオ:
  - test_install_enable_disable_remove_store: ストアからのプラグインインストール・有効化・無効化・削除
  - test_install_enable_disable_remove_local: ローカルプラグインのインストール・有効化・無効化・削除
  - test_install_update_remove_store/local: プラグインアップデート機能
  - test_install_enable_disable_enable_disable_remove: 複数回の有効化・無効化テスト
  - ファイルアップロード制限時のテスト

### フロント画面テスト (EF系)

#### EF01 - TOPページテスト
- **EF01TopCest.php**: フロント画面TOPページ
- 主要テストシナリオ:
  - EF0101-UC01-T01: TOPページ初期表示
  - EF0101-UC01-T02: TOPページ新着情報表示
  - EF0101-UC02-T01: TOPページカテゴリ検索
  - EF0101-UC03-T01: TOPページ全件検索
  - EF0101-UC03-T02: TOPページカテゴリ絞込検索・キーワード絞込検索

#### EF02 - 商品一覧・詳細テスト
- **EF02ProductCest.php**: 商品表示機能
- 主要テストシナリオ:
  - EF0201-UC01-T01/T02: 商品一覧ページ初期表示
  - 商品一覧ソート機能
  - 商品詳細ページ表示

#### EF03 - 注文処理テスト
- **EF03OrderCest.php**: 注文処理の包括的テスト
- 主要テストシナリオ:
  - 複数配送機能
  - 注文確認機能
  - 決済処理
  - ゲスト購入機能
  - 会員購入機能

#### EF04 - 顧客機能テスト
- **EF04CustomerCest.php**: 顧客関連機能
- 主要テストシナリオ:
  - EF0401-UC01-T01: 会員登録正常パターン
  - EF0401-UC01-T02: 会員登録異常パターン（重複）
  - EF0401-UC01-T03: 会員登録異常パターン（入力ミス）
  - EF0401-UC01-T04: 会員登録同意しないボタン
  - EF0401-UC01-T05: 会員登録戻るボタン
  - EF0401-UC01-T06: 会員登録後ログイン
  - EF0401-UC01-T07: 会員登録後カート機能
  - EF0404-UC01-T01: 利用規約表示

#### EF05 - マイページテスト
- **EF05MypageCest.php**: マイページ機能
- 主要テストシナリオ:
  - マイページ表示
  - 注文履歴確認
  - 会員情報編集

#### EF06 - その他機能テスト
- **EF06OtherCest.php**: その他のフロント機能
- 主要テストシナリオ:
  - お問い合わせ機能
  - サイトマップ表示
  - ログイン機能

#### EF08 - 請求書機能テスト
- **EF08InvoiceCest.php**: 請求書機能
- 主要テストシナリオ:
  - 請求書表示機能
  - 請求書ダウンロード機能

#### EF09 - スロットリングテスト
- **EF09ThrottlingCest.php**: ログイン試行制限機能
- 主要テストシナリオ:
  - EF0901-UC01-T01: フロント画面ログイン（IP制限）
  - EF0901-UC01-T02: フロント画面ログイン（会員制限）
  - EF0901-UC01-T03: 管理画面ログイン（IP制限）
  - EF0901-UC01-T04: 管理画面ログイン（会員制限）
  - EF0901-UC01-T05: 会員登録制限
  - EF0901-UC01-T06: 問い合わせ制限
  - パスワード再発行制限

### プラグインテスト (PL系)

#### PL01-PL10 - 各種プラグインテスト
- **PL01RecommendCest.php**: おすすめ商品プラグイン
- **PL02CouponCest.php**: クーポンプラグイン
- **PL03MailMagazineCest.php**: メルマガプラグイン
- **PL04SalesReportCest.php**: 売上レポートプラグイン
- **PL05RelatedProductCest.php**: 関連商品プラグイン
- **PL06SecurityCheckCest.php**: セキュリティチェックプラグイン
- **PL07ProductReviewCest.php**: 商品レビュープラグイン
- **PL08ApiCest.php**: APIプラグイン
- **PL09SiteKitCest.php**: サイトキットプラグイン
- **PL10GMCCest.php**: Google Merchant Centerプラグイン

### その他のテスト

#### セキュリティテスト
- **CL01DenyCest.php**: ファイルアクセス制限テスト
  - vendorディレクトリの公開制限
  - codeceptionディレクトリの公開制限

#### 外部連携テスト
- **VaddyCest.php**: Vaddy脆弱性検査連携テスト

#### インストーラーテスト
- **ZZ99InstallerCest.php**: インストーラー機能テスト
- **AA0PluginInstallerCest.php**: プラグインインストーラーテスト
- **ZZ99PluginUninstallerCest.php**: プラグインアンインストーラーテスト

## 未実装テストケースの提案

### 1. セキュリティテスト強化

#### SEC01 - CSRF攻撃対策テスト
```php
// 提案テストケース: SEC01CSRFCest.php
public function csrf_管理画面商品編集(AcceptanceTester $I)
{
    $I->wantTo('SEC0101-UC01-T01 CSRF攻撃対策 - 管理画面商品編集');
    // CSRFトークンなしでの商品編集リクエストを送信
    // 403エラーが返されることを確認
}

public function csrf_フロント会員登録(AcceptanceTester $I)
{
    $I->wantTo('SEC0101-UC01-T02 CSRF攻撃対策 - フロント会員登録');
    // CSRFトークンなしでの会員登録リクエストを送信
    // エラーが返されることを確認
}
```

#### SEC02 - SQLインジェクション対策テスト
```php
// 提案テストケース: SEC02SQLInjectionCest.php
public function sqli_商品検索(AcceptanceTester $I)
{
    $I->wantTo('SEC0201-UC01-T01 SQLインジェクション対策 - 商品検索');
    // 悪意のあるSQLを含む検索クエリを送信
    // 正常にエスケープされることを確認
}

public function sqli_顧客検索(AcceptanceTester $I)
{
    $I->wantTo('SEC0201-UC01-T02 SQLインジェクション対策 - 顧客検索');
    // 管理画面での顧客検索にSQLインジェクションを試行
    // 適切に防御されることを確認
}
```

### 2. パフォーマンステスト

#### PERF01 - 大量データ処理テスト
```php
// 提案テストケース: PERF01LoadCest.php
public function load_大量商品表示(AcceptanceTester $I)
{
    $I->wantTo('PERF0101-UC01-T01 大量商品データでの一覧表示性能');
    // 1000件以上の商品データを作成
    // 商品一覧ページの表示時間を測定
    // 許容範囲内での表示を確認
}

public function load_大量注文処理(AcceptanceTester $I)
{
    $I->wantTo('PERF0101-UC01-T02 大量注文データでの管理画面性能');
    // 大量の注文データを作成
    // 受注管理画面の表示性能を確認
}
```

### 3. モバイル対応テスト

#### MOB01 - レスポンシブデザインテスト
```php
// 提案テストケース: MOB01ResponsiveCest.php
public function mobile_商品一覧表示(AcceptanceTester $I)
{
    $I->wantTo('MOB0101-UC01-T01 モバイル画面での商品一覧表示');
    // モバイルビューポートに設定
    // 商品一覧ページの表示確認
    // レスポンシブレイアウトの確認
}

public function mobile_注文処理(AcceptanceTester $I)
{
    $I->wantTo('MOB0101-UC01-T02 モバイル画面での注文処理');
    // モバイル環境での注文フロー確認
    // タッチ操作の確認
}
```

### 4. 決済処理統合テスト

#### PAY01 - 決済方法統合テスト
```php
// 提案テストケース: PAY01IntegrationCest.php
public function payment_クレジットカード決済(AcceptanceTester $I)
{
    $I->wantTo('PAY0101-UC01-T01 クレジットカード決済統合テスト');
    // テスト用クレジットカード情報での決済
    // 決済完了までのフロー確認
    // 注文ステータスの更新確認
}

public function payment_銀行振込(AcceptanceTester $I)
{
    $I->wantTo('PAY0101-UC01-T02 銀行振込決済テスト');
    // 銀行振込選択時の処理確認
    // 振込情報表示の確認
}
```

### 5. 在庫管理エッジケーステスト

#### INV01 - 在庫管理テスト
```php
// 提案テストケース: INV01StockCest.php
public function stock_在庫切れ商品購入(AcceptanceTester $I)
{
    $I->wantTo('INV0101-UC01-T01 在庫切れ商品の購入試行');
    // 在庫0の商品をカートに追加試行
    // エラーメッセージの表示確認
}

public function stock_同時購入競合(AcceptanceTester $I)
{
    $I->wantTo('INV0101-UC01-T02 在庫競合時の処理');
    // 複数ユーザーでの同時購入シナリオ
    // 在庫の整合性確認
}
```

### 6. メール送信機能テスト

#### MAIL01 - メール送信テスト
```php
// 提案テストケース: MAIL01NotificationCest.php
public function mail_会員登録完了メール(AcceptanceTester $I)
{
    $I->wantTo('MAIL0101-UC01-T01 会員登録完了メール送信');
    // 会員登録後のメール送信確認
    // メール内容の確認
}

public function mail_注文完了メール(AcceptanceTester $I)
{
    $I->wantTo('MAIL0101-UC01-T02 注文完了メール送信');
    // 注文完了後のメール送信確認
    // 注文詳細情報の確認
}
```

### 7. API統合テスト

#### API01 - API機能テスト
```php
// 提案テストケース: API01IntegrationCest.php
public function api_商品情報取得(AcceptanceTester $I)
{
    $I->wantTo('API0101-UC01-T01 API経由での商品情報取得');
    // REST API経由での商品データ取得
    // レスポンス形式の確認
    // 認証機能の確認
}

public function api_注文情報更新(AcceptanceTester $I)
{
    $I->wantTo('API0101-UC01-T02 API経由での注文情報更新');
    // API経由での注文ステータス更新
    // 権限チェックの確認
}
```

### 8. データ整合性テスト

#### DATA01 - データ整合性テスト
```php
// 提案テストケース: DATA01ConsistencyCest.php
public function data_商品削除時の整合性(AcceptanceTester $I)
{
    $I->wantTo('DATA0101-UC01-T01 商品削除時のデータ整合性');
    // 注文履歴のある商品の削除試行
    // 関連データの整合性確認
}

public function data_顧客削除時の整合性(AcceptanceTester $I)
{
    $I->wantTo('DATA0101-UC01-T02 顧客削除時のデータ整合性');
    // 注文履歴のある顧客の削除試行
    // 関連データの処理確認
}
```

### 9. 国際化・多言語対応テスト

#### I18N01 - 国際化テスト
```php
// 提案テストケース: I18N01LocalizationCest.php
public function i18n_英語表示(AcceptanceTester $I)
{
    $I->wantTo('I18N0101-UC01-T01 英語環境での表示確認');
    // ロケール設定を英語に変更
    // 各画面の表示確認
}

public function i18n_通貨表示(AcceptanceTester $I)
{
    $I->wantTo('I18N0101-UC01-T02 異なる通貨での価格表示');
    // 通貨設定の変更
    // 価格表示形式の確認
}
```

### 10. アクセシビリティテスト

#### A11Y01 - アクセシビリティテスト
```php
// 提案テストケース: A11Y01AccessibilityCest.php
public function a11y_キーボードナビゲーション(AcceptanceTester $I)
{
    $I->wantTo('A11Y0101-UC01-T01 キーボードのみでのナビゲーション');
    // Tabキーでのフォーカス移動確認
    // Enterキーでの操作確認
}

public function a11y_スクリーンリーダー対応(AcceptanceTester $I)
{
    $I->wantTo('A11Y0101-UC01-T02 スクリーンリーダー対応確認');
    // alt属性の設定確認
    // aria-label属性の確認
}
```

## 実装優先度

### 高優先度
1. **セキュリティテスト** (SEC01, SEC02) - セキュリティは最重要
2. **在庫管理エッジケース** (INV01) - ECサイトの核心機能
3. **決済処理統合テスト** (PAY01) - 売上に直結する重要機能
4. **メール送信機能テスト** (MAIL01) - 顧客体験に重要

### 中優先度
1. **データ整合性テスト** (DATA01) - システムの信頼性確保
2. **パフォーマンステスト** (PERF01) - スケーラビリティ確保
3. **API統合テスト** (API01) - 外部連携機能

### 低優先度
1. **モバイル対応テスト** (MOB01) - 既存の機能テストで部分的にカバー
2. **国際化テスト** (I18N01) - 日本市場向けのため優先度低
3. **アクセシビリティテスト** (A11Y01) - 重要だが段階的実装可能

## まとめ

EC-CUBEのcodeceptionテストスイートは、管理画面（EA系）、フロント画面（EF系）、プラグイン（PL系）の各機能について包括的なテストカバレッジを提供しています。特に以下の点で優れています：

### 現在の強み
- 基本的なCRUD操作の網羅的テスト
- ユーザー認証・認可機能の十分なテスト
- プラグインシステムの包括的テスト
- スロットリング機能などのセキュリティ対策テスト

### 改善が必要な領域
- セキュリティテストの強化（CSRF、SQLインジェクション）
- パフォーマンステストの追加
- 決済処理の統合テスト
- エッジケースやエラーハンドリングのテスト
- データ整合性テスト

提案した新しいテストケースを段階的に実装することで、EC-CUBEの品質とセキュリティをさらに向上させることができます。
