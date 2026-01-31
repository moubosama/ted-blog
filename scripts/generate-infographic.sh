#!/bin/bash

# ===========================================
# 図解HTML → PNG 変換スクリプト
# 使い方: ./scripts/generate-infographic.sh input.html output.png
# ===========================================

set -e

# 引数チェック
if [ $# -lt 2 ]; then
    echo "使い方: $0 <input.html> <output.png>"
    echo "例: $0 temp-infographic.html spending-wisely-infographic.png"
    exit 1
fi

INPUT_HTML="$1"
OUTPUT_PNG="$2"
TEMP_PNG="${OUTPUT_PNG%.png}-temp.png"

# 入力ファイル存在チェック
if [ ! -f "$INPUT_HTML" ]; then
    echo "エラー: $INPUT_HTML が見つかりません"
    exit 1
fi

echo "📸 HTML → PNG 変換中..."
echo "   入力: $INPUT_HTML"
echo "   出力: $OUTPUT_PNG"

# wkhtmltoimageで変換
wkhtmltoimage \
    --width 900 \
    --quality 95 \
    --enable-local-file-access \
    "$INPUT_HTML" "$TEMP_PNG"

# ImageMagickで圧縮（200KB以下を目指す）
echo "🗜️  画像を圧縮中..."
convert "$TEMP_PNG" \
    -quality 85 \
    -strip \
    "$OUTPUT_PNG"

# 一時ファイル削除
rm -f "$TEMP_PNG"

# サイズ確認
SIZE=$(ls -lh "$OUTPUT_PNG" | awk '{print $5}')
echo "✅ 完了: $OUTPUT_PNG ($SIZE)"

# 200KB超えの場合は警告
SIZE_BYTES=$(stat -f%z "$OUTPUT_PNG" 2>/dev/null || stat -c%s "$OUTPUT_PNG" 2>/dev/null)
if [ "$SIZE_BYTES" -gt 204800 ]; then
    echo "⚠️  警告: ファイルサイズが200KBを超えています"
fi
