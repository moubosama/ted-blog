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
    background: url('https://images.unsplash.com/photo-1559757175-5700dde675bc?w=1200&q=80&fit=crop') center/cover no-repeat;
  }
  .overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(20,15,40,0.94) 0%, rgba(30,20,60,0.8) 50%, rgba(40,25,70,0.6) 100%);
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
    font-size: 52px;
    font-weight: 900;
    line-height: 1.4;
    margin-bottom: 20px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
  }
  .main-title .accent {
    color: #c9a0ff;
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
      <div class="ted-label">TED-Ed</div>
      <div class="main-title"><span class="accent">催眠術</span>は本当に効くのか？<br>科学が出した意外な答え</div>
      <div class="subtitle">手術中の痛み軽減、オピオイド削減...医学的効果の真実</div>
    </div>
    <div class="speaker">Devin Terhune</div>
  </div>
</body>
</html>`;

  await page.setContent(html, { waitUntil: 'networkidle0', timeout: 15000 });

  const outputPath = path.join(__dirname, 'static', 'images', 'posts', 'hypnosis.jpg');
  await page.screenshot({
    path: outputPath,
    type: 'jpeg',
    quality: 90
  });
  await browser.close();
  console.log(`Generated: ${outputPath}`);
})();
