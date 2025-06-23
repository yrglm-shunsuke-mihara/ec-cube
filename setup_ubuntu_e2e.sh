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

log_info "🚀 Ubuntu 22.04でEC-CUBE E2Eテスト環境構築を開始します"

# 1. 必要なパッケージのインストール（GitHub Actionsと同様）
log_info "必要なパッケージをインストール中..."
sudo apt update
sudo apt install -y \
    fonts-ipafont \
    fonts-ipaexfont \
    wget \
    unzip \
    xvfb \
    google-chrome-stable \
    curl

# Chrome Driverのインストール
log_info "Chrome Driverをインストール中..."
CHROME_VERSION=$(google-chrome --version | cut -d" " -f3 | cut -d"." -f1)
CHROMEDRIVER_VERSION=$(curl -s "https://chromedriver.storage.googleapis.com/LATEST_RELEASE_$CHROME_VERSION")

if [ ! -f "/usr/local/bin/chromedriver" ] || [ "$(/usr/local/bin/chromedriver --version | cut -d" " -f2)" != "$CHROMEDRIVER_VERSION" ]; then
    wget -O /tmp/chromedriver.zip "https://chromedriver.storage.googleapis.com/$CHROMEDRIVER_VERSION/chromedriver_linux64.zip"
    sudo unzip -o /tmp/chromedriver.zip -d /usr/local/bin/
    sudo chmod +x /usr/local/bin/chromedriver
    rm /tmp/chromedriver.zip
fi

# 2. PostgreSQLとMailcatcherをDockerで起動
log_info "PostgreSQLとMailcatcherを起動中..."
docker-compose -f - up -d <<EOF
version: "3"
services:
  postgres:
    image: postgres:14
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: password
      POSTGRES_DB: eccube_db
    ports:
      - "5432:5432"
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 10s
      timeout: 5s
      retries: 5

  mailcatcher:
    image: schickling/mailcatcher
    ports:
      - "1080:1080"
      - "1025:1025"
EOF

# PostgreSQLの起動を待機
log_info "PostgreSQLの起動を待機中..."
timeout 60 bash -c 'until docker exec $(docker ps -q --filter "ancestor=postgres:14") pg_isready -U postgres; do sleep 2; done'

# 3. Composerの依存関係インストール
log_info "Composerの依存関係をインストール中..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 4. npmの依存関係インストール・ビルド
log_info "npmの依存関係をインストール・ビルド中..."
npm ci
npm run build

# 5. 環境変数の設定
log_info "環境変数を設定中..."
cat > .env << EOF
APP_ENV=codeception
APP_DEBUG=1
DATABASE_URL=postgres://postgres:password@127.0.0.1:5432/eccube_db
DATABASE_SERVER_VERSION=14
DATABASE_CHARSET=utf8
MAILER_DSN=smtp://127.0.0.1:1025
ECCUBE_AUTH_MAGIC=test_magic_key
TRUSTED_HOSTS=127.0.0.1,localhost
ECCUBE_PACKAGE_API_URL=http://127.0.0.1:8080
EOF

# 6. データベースの準備
log_info "データベースを準備中..."
bin/console doctrine:database:create --env=dev --if-not-exists
bin/console doctrine:schema:create --env=dev
bin/console eccube:fixtures:load --env=dev --no-interaction

log_success "✅ 環境構築が完了しました！"
log_info "次のコマンドでテストを実行できます："
echo ""
echo "# すべてのテストを実行"
echo "./run_ubuntu_e2e_tests.sh"
echo ""
echo "# 特定のグループのみ実行"
echo "./run_ubuntu_e2e_tests.sh admin01"
echo "./run_ubuntu_e2e_tests.sh front" 