#!/bin/bash

set -e

# 色付きログのための関数
log_info() {
    echo -e "\033[34m[INFO]\033[0m $1"
}

log_success() {
    echo -e "\033[32m[SUCCESS]\033[0m $1"
}

log_error() {
    echo -e "\033[31m[ERROR]\033[0m $1"
}

# テストグループを指定
TEST_GROUP=${1:-admin01}

log_info "🧪 EC-CUBE E2Eテストを実行します (グループ: $TEST_GROUP)"

# 1. 環境変数の設定
export APP_ENV=codeception
export DATABASE_URL=postgres://postgres:password@127.0.0.1:5432/eccube_db
export DATABASE_SERVER_VERSION=14
export MAILER_DSN=smtp://127.0.0.1:1025
export ECCUBE_PACKAGE_API_URL=http://127.0.0.1:8080
export SYMFONY_DEPRECATIONS_HELPER=weak

# 2. Mock Package APIの起動（プラグインテスト用）
log_info "Mock Package APIを起動中..."
if [[ ! -d ${PWD}/repos ]]; then 
    mkdir -p ${PWD}/repos
fi

# プラグインファイルの準備
for d in $(ls codeception/_data/plugins | grep 1.0.0 2>/dev/null || true)
do
    if [[ -d "codeception/_data/plugins/$d" ]]; then
        (cd codeception/_data/plugins/$d; tar zcf ../../../../repos/${d}.tgz *)
    fi
done

# Mock Package API起動
docker run -d --rm --name mock-package-api \
    -v ${PWD}/repos:/repos \
    -e MOCK_REPO_DIR=/repos \
    -p 8080:8080 \
    eccube/mock-package-api:composer2 || true

sleep 5

# 3. Xvfbとchromedriverの起動（GitHub Actionsと同様）
log_info "Xvfbとchromedriverを起動中..."
export DISPLAY=:99
sudo Xvfb -ac :99 -screen 0 1280x1024x24 > /dev/null 2>&1 &
XVFB_PID=$!

chromedriver --url-base=/wd/hub --port=9515 > /dev/null 2>&1 &
CHROMEDRIVER_PID=$!

sleep 5

# 4. PHPビルトインサーバーの起動
log_info "PHPビルトインサーバーを起動中..."
php -S 127.0.0.1:8000 codeception/router.php > /dev/null 2>&1 &
PHP_SERVER_PID=$!

sleep 5

# 5. Codeceptionテストの実行
log_info "Codeceptionテストを実行中 (グループ: $TEST_GROUP)..."

# 環境設定ファイルのパス設定
sed -i "s|%PWD%|${PWD}|g" codeception/_envs/devin.yml

# テスト実行
if [[ "$TEST_GROUP" == "restrict-fileupload" ]]; then
    export ECCUBE_RESTRICT_FILE_UPLOAD=1
    vendor/bin/codecept run acceptance \
        --env chrome,devin \
        -g $TEST_GROUP \
        --html report.html \
        -vvv || TEST_RESULT=$?
else
    vendor/bin/codecept run acceptance \
        --env chrome,devin \
        -g $TEST_GROUP \
        --skip-group restrict-file-upload \
        --html report.html \
        -vvv || TEST_RESULT=$?
fi

# 6. スクリーンショットの取得（テスト終了後）
log_info "スクリーンショットを取得中..."
google-chrome --headless --no-sandbox --disable-gpu --screenshot=front_top.png --virtual-time-budget=10000 http://127.0.0.1:8000 > /dev/null 2>&1 || true
google-chrome --headless --no-sandbox --disable-gpu --screenshot=admin_top.png --virtual-time-budget=10000 http://127.0.0.1:8000/admin > /dev/null 2>&1 || true

# 7. クリーンアップ
log_info "クリーンアップ中..."

# プロセス停止
kill $PHP_SERVER_PID 2>/dev/null || true
kill $CHROMEDRIVER_PID 2>/dev/null || true
sudo kill $XVFB_PID 2>/dev/null || true

# Mock Package API停止
docker stop mock-package-api 2>/dev/null || true

# 8. 結果の表示
if [[ ${TEST_RESULT:-0} -eq 0 ]]; then
    log_success "✅ E2Eテストが正常に完了しました"
    log_info "📊 テスト結果："
    echo "  - HTMLレポート: report.html"
    echo "  - スクリーンショット: front_top.png, admin_top.png"
    echo "  - テスト出力: codeception/_output/"
    echo ""
    echo "📖 HTMLレポートを開くには："
    echo "  xdg-open report.html"
else
    log_error "❌ E2Eテストでエラーが発生しました"
    log_info "📊 エラー詳細は以下を確認してください："
    echo "  - HTMLレポート: report.html"
    echo "  - テスト出力: codeception/_output/"
    echo "  - アプリケーションログ: var/log/"
fi

exit ${TEST_RESULT:-0} 