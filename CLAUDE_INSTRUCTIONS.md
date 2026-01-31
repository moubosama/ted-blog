# TED要約ラボ - Claude Code 指示書

## プロジェクト概要

TED講演を日本語で要約するブログメディア。
- サイト: https://moubosama.github.io/ted-blog/
- 技術スタック: Hugo + PaperMod + GitHub Pages

---

## 📝 記事作成コマンド

### 基本コマンド

```
記事作成: [TED URL]
```

例：
```
記事作成: https://www.ted.com/talks/wolfgang_schnellbaecher_are_you_spending_your_money_wisely
```

### 実行手順

1. **トランスクリプト取得**
   - TED URLからタイトル・講演者・内容を取得
   - 日本語字幕があれば優先、なければ英語から要約

2. **記事生成**（memo_improved.md のルールに従う）
   - 1,500〜2,000字
   - 固定フォーマット：一言要約→講演者→3ポイント→アクション→感想
   - 禁止事項を守る（説教調、SNSタグ、架空人格など）

3. **図解生成**
   ```bash
   # テンプレートをコピーして編集
   cp templates/infographic-template.html temp-infographic.html
   # 内容を書き換え
   # PNG変換
   ./scripts/generate-infographic.sh temp-infographic.html [output-name].png
   ```

4. **ファイル配置**
   ```
   content/posts/YYYY-MM-DD-[slug].md
   static/images/posts/[slug].jpg          # アイキャッチ
   static/images/posts/[slug]-infographic.png  # 図解
   ```

5. **デプロイ**
   ```bash
   git add .
   git commit -m "Add: [記事タイトル]"
   git push
   ```

6. **完了報告**
   ```
   ✅ 記事を公開しました
   URL: https://moubosama.github.io/ted-blog/posts/YYYY-MM-DD-[slug]/
   ```

---

## 🎨 図解作成コマンド

### 基本コマンド

```
図解作成: [記事のslug]
```

すでに記事がある場合に図解だけ追加する。

### 図解のルール

- 講演の3つのポイントを視覚化
- 各ポイントに「ありがちな失敗」と「プロの行動」の比較
- ダークテーマ（#1a1a2e、#16213e）
- サイズ: 800x1000px程度
- 圧縮後200KB以下

---

## 📁 ディレクトリ構造

```
ted-blog/
├── content/
│   └── posts/           # 記事Markdown
├── static/
│   └── images/
│       └── posts/       # 画像（アイキャッチ・図解）
├── templates/
│   └── infographic-template.html
├── scripts/
│   └── generate-infographic.sh
├── memo_improved.md     # 記事作成ガイドライン
└── CLAUDE_INSTRUCTIONS.md  # このファイル
```

---

## ✅ 記事チェックリスト

公開前に確認：

- [ ] タイトルが30字以内
- [ ] descriptionが120字以内
- [ ] 本文が1,500〜2,000字
- [ ] 3つのポイントが明確
- [ ] 具体的なアクションがある
- [ ] TED公式リンクがある
- [ ] アイキャッチ画像がある
- [ ] 図解画像がある（3ポイントの後）
- [ ] `[SNS_START]`等のタグが本文にない
- [ ] 「編集長」「編集部」の表現がない

---

## 🚫 禁止事項（memo_improved.mdから）

- `[SNS_START]`等のタグを本文に含めない
- 「編集長」「編集部」などの架空人格禁止
- 「再現性チェック」「辛口視点」セクション禁止
- 日本企業批判や社会批評を長々と書かない
- 読者を「凡人」や「消費」していると表現しない
- 説教調の導入（「あなたはすべき」「しなければならない」）

---

## 💡 よく使うコマンド

### Hugo ローカルプレビュー
```bash
cd ted-blog
hugo server -D
# http://localhost:1313/ted-blog/ で確認
```

### 記事一覧確認
```bash
ls -la content/posts/
```

### 画像一覧確認
```bash
ls -la static/images/posts/
```

### デプロイ状況確認
```bash
git log --oneline -5
```

---

## 🔗 参考リンク

- [TED公式](https://www.ted.com/)
- [Hugo PaperMod](https://github.com/adityatelange/hugo-PaperMod)
- [GitHub Pages](https://pages.github.com/)
