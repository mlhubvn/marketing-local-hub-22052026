import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = path.dirname(fileURLToPath(import.meta.url));
const dataDir = path.join(dir, '../data');
const dataPath = path.join(dataDir, 'ai_templates.json');
const START_ID = 147123468;

const categoryMap = {
  Suggested: 'Gợi ý',
  Facebook: 'Facebook',
  Instagram: 'Instagram',
  'X (Twitter)': 'X (Twitter)',
  LinkedIn: 'LinkedIn',
  Pinterest: 'Pinterest',
  'Google Business Profile': 'Google Business Profile',
  TikTok: 'TikTok',
  Youtube: 'Youtube',
  Rewrite: 'Viết lại',
  Edit: 'Chỉnh sửa',
  'Explain & Expand': 'Giải thích & Mở rộng',
  Summarize: 'Tóm tắt',
  'Psychological Frameworks': 'Khung tâm lý',
  'Content Creation Frameworks': 'Khung sáng tạo nội dung',
  Ads: 'Quảng cáo',
  'Company-Related': 'Doanh nghiệp',
  'CTA Prompts': 'Kêu gọi hành động (CTA)',
  Educational: 'Giáo dục',
  'Fun Prompts': 'Vui vẻ',
  Interactive: 'Tương tác',
  Inspirational: 'Truyền cảm hứng',
  Holiday: 'Ngày lễ',
  'Small Businesses': 'Hộ kinh doanh nhỏ',
  'Business Coaches': 'Huấn luyện kinh doanh',
  'Lifestyle Coaches': 'Huấn luyện lối sống',
  Realtors: 'Môi giới bất động sản',
  NGOs: 'Tổ chức phi lợi nhuận',
  Entreprenuers: 'Doanh nhân',
  'Marketing Agencies': 'Agency marketing',
  Authors: 'Tác giả',
  'Accounting Firms': 'Công ty kế toán',
  Restaurants: 'Nhà hàng',
  'Fitness Trainers': 'Huấn luyện viên fitness',
};

const viMap = {};
for (let i = 1; i <= 4; i++) {
  const p = path.join(dataDir, `ai_templates_vi_map_${i}.json`);
  if (!fs.existsSync(p)) {
    console.error(`Missing ${p}`);
    process.exit(1);
  }
  Object.assign(viMap, JSON.parse(fs.readFileSync(p, 'utf8')));
}

const items = JSON.parse(fs.readFileSync(dataPath, 'utf8'));
const now = Math.floor(Date.now() / 1000);
let missing = 0;

items.forEach((item, index) => {
  item.id = START_ID + index;
  item.category_name = categoryMap[item.category_name] ?? item.category_name;
  if (!viMap[item.id_secure]) {
    missing++;
    console.warn('Missing translation:', item.id_secure);
  }
  item.content = viMap[item.id_secure] ?? item.content;
  item.changed = now;
  item.created = now;
});

fs.writeFileSync(path.join(dataDir, 'ai_templates_vi_map.json'), JSON.stringify(viMap, null, 2));
fs.writeFileSync(dataPath, `${JSON.stringify(items, null, 4)}\n`);

console.log(`Merged ${Object.keys(viMap).length} translations into ${items.length} templates`);
console.log(`Missing: ${missing}`);
