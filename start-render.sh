#!/bin/sh
set -eu

APP_ROOT="/app"
DATA_ROOT="${APP_DATA_DIR:-/var/data}"

if [ ! -d "$DATA_ROOT" ]; then
    DATA_ROOT="$APP_ROOT/.render-data"
fi

mkdir -p "$DATA_ROOT/api"

seed_and_link() {
    relative_path="$1"
    source_path="$APP_ROOT/$relative_path"
    target_path="$DATA_ROOT/$relative_path"

    mkdir -p "$(dirname "$target_path")"
    mkdir -p "$(dirname "$source_path")"

    if [ -e "$source_path" ] && [ ! -e "$target_path" ]; then
        cp "$source_path" "$target_path"
    fi

    if [ -e "$target_path" ]; then
        rm -f "$source_path"
        ln -s "$target_path" "$source_path"
    fi
}

touch_and_link() {
    relative_path="$1"
    source_path="$APP_ROOT/$relative_path"
    target_path="$DATA_ROOT/$relative_path"

    mkdir -p "$(dirname "$target_path")"
    mkdir -p "$(dirname "$source_path")"

    touch "$target_path"
    rm -f "$source_path"
    ln -s "$target_path" "$source_path"
}

seed_and_link "admin_messages.json"
seed_and_link "audit_log.jsonl"
seed_and_link "bank_accounts.json"
seed_and_link "email_log.txt"
seed_and_link "messages.json"
seed_and_link "password_resets.json"
seed_and_link "pending_transfers.json"
seed_and_link "reset_links.txt"
seed_and_link "transactions.json"
seed_and_link "users.json"
seed_and_link "withdrawal_codes.json"
seed_and_link "api/exchange_rates_cache.json"

touch_and_link "withdrawal_attempts.log"

exec php -S 0.0.0.0:"${PORT:-10000}" -t "$APP_ROOT"