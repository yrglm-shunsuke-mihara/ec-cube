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
 * @group api
 * @group integration
 * @group rest
 */
class API01IntegrationCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        $I->resetCookie('PHPSESSID');
    }

    /**
     * 商品API統合テスト - 商品一覧取得
     */
    public function api_integration_商品一覧取得(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T01 商品API統合テスト - 商品一覧取得');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendGET('/api/products');
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'success'
        ]);
        
        $I->seeResponseJsonMatchesJsonPath('$.data[*].id');
        $I->seeResponseJsonMatchesJsonPath('$.data[*].name');
        $I->seeResponseJsonMatchesJsonPath('$.data[*].price');
        $I->seeResponseJsonMatchesJsonPath('$.data[*].stock');
        
        $response = json_decode($I->grabResponse(), true);
        $I->assertGreaterThan(0, count($response['data']));
    }

    /**
     * 商品API統合テスト - 商品詳細取得
     */
    public function api_integration_商品詳細取得(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T02 商品API統合テスト - 商品詳細取得');
        
        $productId = $I->grabFromDatabase('dtb_product', 'id', [], 'ORDER BY id LIMIT 1');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendGET("/api/products/{$productId}");
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'success',
            'data' => [
                'id' => (int)$productId
            ]
        ]);
        
        $I->seeResponseJsonMatchesJsonPath('$.data.name');
        $I->seeResponseJsonMatchesJsonPath('$.data.description_detail');
        $I->seeResponseJsonMatchesJsonPath('$.data.price');
        $I->seeResponseJsonMatchesJsonPath('$.data.stock');
        $I->seeResponseJsonMatchesJsonPath('$.data.category');
    }

    /**
     * 商品API統合テスト - 存在しない商品
     */
    public function api_integration_存在しない商品(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T03 商品API統合テスト - 存在しない商品');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendGET('/api/products/99999');
        
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'error',
            'message' => '商品が見つかりません'
        ]);
    }

    /**
     * カテゴリAPI統合テスト
     */
    public function api_integration_カテゴリ一覧取得(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T04 カテゴリAPI統合テスト');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendGET('/api/categories');
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'success'
        ]);
        
        $I->seeResponseJsonMatchesJsonPath('$.data[*].id');
        $I->seeResponseJsonMatchesJsonPath('$.data[*].name');
        $I->seeResponseJsonMatchesJsonPath('$.data[*].level');
        $I->seeResponseJsonMatchesJsonPath('$.data[*].sort_no');
        
        $response = json_decode($I->grabResponse(), true);
        $I->assertGreaterThan(0, count($response['data']));
    }

    /**
     * 注文API統合テスト - 認証必須
     */
    public function api_integration_注文情報取得_認証必須(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T05 注文API統合テスト - 認証必須');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendGET('/api/orders');
        
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'error',
            'message' => '認証が必要です'
        ]);
    }

    /**
     * 検索API統合テスト
     */
    public function api_integration_商品検索(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T06 検索API統合テスト');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $searchParams = [
            'keyword' => 'テスト',
            'category_id' => 1,
            'price_min' => 100,
            'price_max' => 10000
        ];
        
        $I->sendGET('/api/products/search?' . http_build_query($searchParams));
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'success'
        ]);
        
        $I->seeResponseJsonMatchesJsonPath('$.data');
        $I->seeResponseJsonMatchesJsonPath('$.pagination.total');
        $I->seeResponseJsonMatchesJsonPath('$.pagination.page');
        $I->seeResponseJsonMatchesJsonPath('$.pagination.limit');
        
        $response = json_decode($I->grabResponse(), true);
        $I->assertArrayHasKey('data', $response);
        $I->assertArrayHasKey('pagination', $response);
    }

    /**
     * API認証テスト - APIキー
     */
    public function api_integration_API認証_APIキー(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T07 API認証テスト - APIキー');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('X-API-Key', 'invalid_api_key');
        
        $I->sendGET('/api/admin/products');
        
        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'error',
            'message' => '無効なAPIキーです'
        ]);
    }

    /**
     * APIレート制限テスト
     */
    public function api_integration_レート制限(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T08 APIレート制限テスト');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        for ($i = 0; $i < 10; $i++) {
            $I->sendGET('/api/products');
            
            if ($i < 5) {
                $I->seeResponseCodeIs(200);
            } else {
                $responseCode = $I->grabResponseCode();
                if ($responseCode === 429) {
                    $I->seeResponseContainsJson([
                        'status' => 'error',
                        'message' => 'レート制限に達しました'
                    ]);
                    break;
                }
            }
        }
    }

    /**
     * APIエラーハンドリングテスト
     */
    public function api_integration_エラーハンドリング(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T09 APIエラーハンドリングテスト');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendPOST('/api/products', '{"invalid": json}');
        
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        
        $I->seeResponseContainsJson([
            'status' => 'error'
        ]);
        
        $I->seeResponseJsonMatchesJsonPath('$.message');
    }

    /**
     * APIバージョニングテスト
     */
    public function api_integration_バージョニング(AcceptanceTester $I)
    {
        $I->wantTo('API0101-UC01-T10 APIバージョニングテスト');
        
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        
        $I->sendGET('/api/v1/products');
        $I->seeResponseCodeIsNot(404);
        
        $I->sendGET('/api/v2/products');
        $responseCode = $I->grabResponseCode();
        
        if ($responseCode === 404) {
            $I->seeResponseContainsJson([
                'status' => 'error',
                'message' => 'APIバージョンが見つかりません'
            ]);
        } else {
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
        }
    }
}
