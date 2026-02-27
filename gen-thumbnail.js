const puppeteer = require('puppeteer');
const path = require('path');

(async () => {
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
    background: url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&q=80&fit=crop') center/cover no-repeat;
  }
  .overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(25,35,55,0.94) 0%, rgba(25,45,70,0.8) 50%, rgba(30,50,80,0.6) 100%);
  }
  .content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 80px;
    color: #fff;
  }
  .ted-label {
    display: inline-block;
    background: #e62b1e;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    padding: 6px 16px;
    border-radius: 4px;
    margin-bottom: 24px;
    width: fit-content;
  }
  .main-title {
    font-size: 48px;
    font-weight: 900;
    line-height: 1.4;
    margin-bottom: 20px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
  }
  .main-title .accent {
    color: #64b5f6;
  }
  .subtitle {
    font-size: 22px;
    font-weight: 400;
    opacity: 0.9;
    line-height: 1.6;
  }
  .speaker {
    position: absolute;
    bottom: 40px;
    right: 60px;
    font-size: 18px;
    opacity: 0.8;
  }
</style>
</head>
<body>
  <div class="container">
    <div class="overlay"></div>
    <div class="content">
      <div class="ted-label">TED要約</div>
      <div class="main-title"><span class="accent">変化が怖い</span>人へ：<br>企業変革の75%が失敗する本当の理由</div>
      <div class="subtitle">行動科学者が教える「変化を乗り越える」具体的ツール</div>
    </div>
    <div class="speaker">Kristy Ellmer</div>
  </div>
</body>
</html>`;

  await page.setContent(html, { waitUntil: 'networkidle0', timeout: 15000 });

  const outputPath = path.join(__dirname, 'static', 'images', 'posts', 'navigating-change.jpg');
  await page.screenshot({
    path: outputPath,
    type: 'jpeg',
    quality: 90
  });
  await browser.close();
  console.log(`Generated: ${outputPath}`);
})();
