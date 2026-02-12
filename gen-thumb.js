const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

async function generateThumbnail(config) {
  const browser = await puppeteer.launch({ headless: 'new' });
  const page = await browser.newPage();
  await page.setViewport({ width: 1200, height: 630 });

  const html = `<!DOCTYPE html>
<html>
<head>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;900&display=swap');
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    width: 1200px;
    height: 630px;
    font-family: 'Noto Sans JP', sans-serif;
    overflow: hidden;
  }
  .container {
    position: relative;
    width: 1200px;
    height: 630px;
    background: url('${config.bgUrl}') center/cover no-repeat;
  }
  .overlay {
    position: absolute;
    inset: 0;
    background: ${config.overlayGradient};
  }
  .content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 48px 60px;
    color: #fff;
  }
  .category {
    display: inline-block;
    background: #e62b1e;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    padding: 6px 16px;
    border-radius: 4px;
    margin-bottom: 20px;
    width: fit-content;
  }
  .title {
    font-size: ${config.titleSize || '52px'};
    font-weight: 900;
    line-height: 1.3;
    text-shadow: 0 2px 20px rgba(0,0,0,0.5);
    max-width: 900px;
  }
  .highlight {
    color: ${config.highlightColor || '#ffd166'};
  }
  .subtitle {
    font-size: 20px;
    font-weight: 400;
    margin-top: 16px;
    opacity: 0.9;
    text-shadow: 0 1px 10px rgba(0,0,0,0.5);
    max-width: 800px;
  }
  .speaker {
    position: absolute;
    bottom: 24px;
    right: 40px;
    font-size: 15px;
    opacity: 0.7;
    color: #fff;
    text-shadow: 0 1px 5px rgba(0,0,0,0.5);
  }
</style>
</head>
<body>
  <div class="container">
    <div class="overlay"></div>
    <div class="content">
      <div class="category">TED要約</div>
      <div class="title">${config.title}</div>
      <div class="subtitle">${config.subtitle}</div>
    </div>
    <div class="speaker">${config.speaker}</div>
  </div>
</body>
</html>`;

  await page.setContent(html, { waitUntil: 'networkidle0', timeout: 15000 });
  await page.screenshot({
    path: config.outputPath,
    type: 'jpeg',
    quality: 90
  });
  await browser.close();
  console.log(`Generated: ${config.outputPath}`);
}

(async () => {
  const outDir = path.join(__dirname, 'static', 'images', 'posts');

  // 1. scoring-my-life.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(29,53,87,0.85) 0%, rgba(29,53,87,0.4) 100%)',
    title: '人生を毎日<span class="highlight">「採点」</span>したら<br>何が変わったか',
    subtitle: '18ヶ月間、毎晩90秒で9項目をスコアリングした結果',
    speaker: 'Chris Musser',
    outputPath: path.join(outDir, 'scoring-my-life.jpg')
  });

  // 2. sitting-all-day.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1497215842964-222b430dc094?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(40,20,20,0.82) 0%, rgba(60,30,30,0.45) 100%)',
    title: '座りすぎが寿命を縮める？<br><span class="highlight">「30分に5分」</span>の科学',
    subtitle: '2万人の実験が証明した、デスクワーカーの最もシンプルな健康ハック',
    speaker: 'Manoush Zomorodi',
    outputPath: path.join(outDir, 'sitting-all-day.jpg')
  });

  // 3. raise-brave-kids.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1536640712-4d4c36ff0e4e?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(20,40,60,0.85) 0%, rgba(30,60,80,0.45) 100%)',
    title: '<span class="highlight">「強い子」</span>に育てる<br>たった1つの公式',
    subtitle: '小児不安の専門家が教える「不安＋勇気＝自信」の法則',
    speaker: 'Kathryn Hecht',
    outputPath: path.join(outDir, 'raise-brave-kids.jpg')
  });

  // 4. losing-everything-resilience.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(50,20,40,0.85) 0%, rgba(30,15,25,0.45) 100%)',
    title: '全部失って気づいた<br><span class="highlight">「本当の強さ」</span>の正体',
    subtitle: '100万人の赤ちゃんを救った起業家が、会社倒産で壊れてから見つけたもの',
    speaker: 'Jane Marie Chen',
    outputPath: path.join(outDir, 'losing-everything-resilience.jpg')
  });

  // 5. love-pill.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(60,20,50,0.85) 0%, rgba(40,10,30,0.45) 100%)',
    title: '<span class="highlight">「全人類を愛せる薬」</span><br>があったら飲む？',
    subtitle: 'アリストテレスとイエスが「愛」について真逆のことを言っていた',
    speaker: 'Meghan Sullivan',
    outputPath: path.join(outDir, 'love-pill.jpg')
  });

  // 6. win-win-win.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(20,50,40,0.85) 0%, rgba(10,30,25,0.45) 100%)',
    title: '<span class="highlight">「勝ち負け思考」</span>の罠から<br>抜け出す方法',
    subtitle: 'Whole Foods創業者が語る「Win-Win-Win」の哲学',
    speaker: 'John Mackey',
    outputPath: path.join(outDir, 'win-win-win.jpg')
  });

  // 7. build-a-bear.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(80,30,30,0.85) 0%, rgba(50,15,15,0.45) 100%)',
    title: '倒産寸前の会社を救った<br><span class="highlight">「たった1つの問い」</span>',
    subtitle: 'Build-A-Bear CEOが5000万ドルの赤字から株価2000%成長を実現した方法',
    speaker: 'Sharon Price John',
    outputPath: path.join(outDir, 'build-a-bear.jpg')
  });

  // 8. teams-fail.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(40,30,60,0.85) 0%, rgba(20,15,35,0.45) 100%)',
    title: 'チームが失敗する<br><span class="highlight">「意外とシンプルな理由」</span>',
    subtitle: 'NASAの火星探査機が墜落した原因は「単位の確認をしなかった」こと',
    speaker: 'Tessa West',
    outputPath: path.join(outDir, 'teams-fail.jpg')
  });

  // 9. appreciation.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(50,40,20,0.85) 0%, rgba(30,25,10,0.45) 100%)',
    title: '脳を変える<span class="highlight">「毎日の習慣」</span><br>マンホールが人生を変えた話',
    subtitle: '最悪の1日に、なぜかマンホールの蓋に感動した。その瞬間から見え方が変わった',
    speaker: 'Timm Chiusano',
    outputPath: path.join(outDir, 'appreciation.jpg')
  });

  // 10. dog-communication.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(30,50,40,0.85) 0%, rgba(15,30,20,0.45) 100%)',
    title: '犬との<span class="highlight">「本当のコミュニケーション」</span><br>を世界チャンピオンに学ぶ',
    subtitle: '声で命令するのは、犬にとって一番不自然。では何が自然か？',
    speaker: 'Jennifer Crank',
    outputPath: path.join(outDir, 'dog-communication.jpg')
  });

  // 11. gottman-relationship.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(60,30,40,0.85) 0%, rgba(40,15,25,0.45) 100%)',
    title: '結婚50年の研究者夫婦が教える<br><span class="highlight">「正しいケンカの仕方」</span>',
    subtitle: 'ケンカの最初の3分で、6年後の関係が96%の精度で予測できる',
    speaker: 'Julie & John Gottman',
    outputPath: path.join(outDir, 'gottman-relationship.jpg')
  });

  // 12. quit-drinking.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(40,40,50,0.85) 0%, rgba(20,20,30,0.45) 100%)',
    title: 'お酒をやめた日、<br><span class="highlight">最初にググったこと</span>',
    subtitle: '「お酒以外に何をすればいいの？」——7年間の問題飲酒をやめた翌日の検索',
    speaker: 'Edith Zimmerman',
    outputPath: path.join(outDir, 'quit-drinking.jpg')
  });

  // 13. mind-reader.jpg (renamed to bypass cache)
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(20,20,40,0.88) 0%, rgba(40,20,60,0.5) 100%)',
    title: '「世界最高のマインドリーダー」が明かす<br><span class="highlight">人の心を読む技術</span>',
    subtitle: '心は読めない。でも「人」は読める。30年かけて学んだスキル',
    speaker: 'Oz Pearlman',
    outputPath: path.join(outDir, 'mind-reader.jpg')
  });

  // 14. work-relationships.jpg
  await generateThumbnail({
    bgUrl: 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1200&h=630&fit=crop',
    overlayGradient: 'linear-gradient(135deg, rgba(30,40,60,0.88) 0%, rgba(20,30,50,0.5) 100%)',
    title: '職場の人間関係、<br><span class="highlight">「頑張れば報われる」は嘘</span>だった',
    subtitle: '徹夜で完璧な仕事をしても評価されなかった心理学者が見つけたもの',
    speaker: 'Alyssa Birnbaum',
    outputPath: path.join(outDir, 'work-relationships.jpg')
  });

  console.log('All thumbnails generated!');
})();
