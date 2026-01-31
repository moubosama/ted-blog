# TED要約ブログ 自動更新ワークフロー

ユーザーが「TEDブログを更新して」「新しいTED記事を作って」等と依頼した場合、以下の手順を実行する。

---

## Step 1: スクレイピング

```bash
php php/scrape.php
```

を実行し、新着TED動画のトランスクリプトを `data/pending/` にJSON形式で保存する。
「No new talks to scrape」と表示された場合、新しい動画がないことをユーザーに伝えて終了。

---

## Step 2: 記事生成

`data/pending/` 内の各JSONファイルを読み、以下のルールに従って日本語ビジネス記事を生成する。

### 入力JSONの構造
```json
{
  "talk_url": "https://www.ted.com/talks/...",
  "original_title": "英語の原題",
  "speaker": "スピーカー名",
  "description": "概要",
  "transcript": "英語トランスクリプト全文",
  "word_count": 2500
}
```

### 記事生成ルール

**ペルソナ**: ビジネスメディアの編集者として振る舞う。

**対象読者**: 日本のビジネスパーソン（30〜50代、管理職・経営層）

**文体**: だ・である調で統一

**記事の構成**:
1. **はじめに** — スピーカーの紹介と話の導入（2〜3段落）
2. **主要ポイント** — トークの核心を3〜5つのセクションで解説。各セクションに `##` 見出しを付ける
3. **ビジネスへの教訓** — 具体的な応用方法を示す
4. **まとめ** — 全体の締めくくり

**タイトル**: 原題の直訳ではなく、日本のビジネスパーソンの興味を引く翻案タイトルを作成

**分量**: 本文1500〜2500文字程度

**タグ**: 関連するタグを3つ生成

**教訓**: ビジネスに転用できる具体的な教訓を3つ抽出（各1〜2文）

**専門用語**: 適切に日本語訳し、必要に応じて英語の原語を括弧で補足

---

## Step 3: Markdownファイル出力

生成した記事を以下のフォーマットで `content/posts/` に書き出す。

ファイル名: `content/posts/{YYYY-MM-DD}-{english-slug}.md`

```markdown
---
title: "日本語タイトル"
date: YYYY-MM-DDTHH:MM:SS+09:00
draft: false
description: "3行以内の要約"
summary: "3行以内の要約"
author: "TED要約ラボ"
speaker: "スピーカー名"
original_url: "https://www.ted.com/talks/..."
tags:
  - "タグ1"
  - "タグ2"
  - "タグ3"
categories:
  - "TED要約"
ShowToc: true
TocOpen: false
---

（本文をここに記述）

---

## ビジネスへの3つの教訓

**1.** 教訓1の内容（1〜2文）

**2.** 教訓2の内容（1〜2文）

**3.** 教訓3の内容（1〜2文）

---

*この記事は [スピーカー名のTEDトーク](元URL) をAIが要約・翻案したものである。*
```

---

## Step 4: 処理済み記録

各記事の生成が完了したら、以下のコマンドを実行してSQLiteに記録する:

```bash
php php/mark-processed.php --url="トークURL" --title="日本語タイトル" --speaker="スピーカー名" --post-path="content/posts/ファイル名.md"
```

---

## Step 5: デプロイ

全記事の生成が完了したら:

```bash
git add content/posts/
git commit -m "Add TED summaries: 記事タイトル1, 記事タイトル2"
git push
```

GitHub Actionsが自動でHugoビルド→GitHub Pagesに公開する。

---

## 注意事項

- トランスクリプトが3000語を超える場合は、冒頭・中盤のハイライト・結末に絞って要約する
- 原文の内容を正確に反映しつつ、日本のビジネス文脈に合わせて翻案する
- 記事タイトルは検索エンジンを意識し、具体的かつ魅力的にする
