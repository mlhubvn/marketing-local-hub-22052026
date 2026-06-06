import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = path.dirname(fileURLToPath(import.meta.url));
const dataDir = path.join(dir, '../../../database/seeders/data');
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

/** @type {Record<string, string>} */
let viMap = {};
for (let i = 1; i <= 4; i++) {
  const p = path.join(dataDir, `ai_templates_vi_map_${i}.json`);
  if (fs.existsSync(p)) {
    viMap = { ...viMap, ...JSON.parse(fs.readFileSync(p, 'utf8')) };
  }
}

const ph = [
  [/\{\{\s*topic\s*\}\}/gi, '{{ chủ đề }}'],
  [/\{\{\s*product\/service\s*\}\}/gi, '{{ sản phẩm/dịch vụ }}'],
  [/\{\{\s*company\/project\s*\}\}/gi, '{{ công ty/dự án }}'],
  [/\{\{\s*product\s*\}\}/gi, '{{ sản phẩm }}'],
  [/\{\{\s*service\s*\}\}/gi, '{{ dịch vụ }}'],
  [/\{\{\s*company\s*\}\}/gi, '{{ công ty }}'],
  [/\{\{\s*brand\s*\}\}/gi, '{{ thương hiệu }}'],
  [/\{\{\s*audience\s*\}\}/gi, '{{ đối tượng }}'],
  [/\{\{\s*niche\s*\}\}/gi, '{{ ngành hàng }}'],
  [/\{\{\s*event\s*\}\}/gi, '{{ sự kiện }}'],
  [/\{\{\s*holiday\s*\}\}/gi, '{{ ngày lễ }}'],
  [/\{\{\s*goal\s*\}\}/gi, '{{ mục tiêu }}'],
  [/\{\{\s*industry\s*\}\}/gi, '{{ ngành }}'],
  [/\{\{\s*location\s*\}\}/gi, '{{ địa điểm }}'],
  [/\{\{\s*name\s*\}\}/gi, '{{ tên }}'],
  [/\{\{\s*business\s*\}\}/gi, '{{ doanh nghiệp }}'],
  [/\{\{\s*offer\s*\}\}/gi, '{{ ưu đãi }}'],
  [/\{\{\s*product name\s*\}\}/gi, '{{ tên sản phẩm }}'],
  [/\{\{\s*new service\s*\}\}/gi, '{{ dịch vụ mới }}'],
  [/\{\{\s*need\s*\}\}/gi, '{{ nhu cầu }}'],
  [/\{\{\s*issue\s*\}\}/gi, '{{ vấn đề }}'],
  [/\{\{\s*ideal customer persona\s*\}\}/gi, '{{ chân dung khách hàng lý tưởng }}'],
  [/\{\{\s*target audience\s*\}\}/gi, '{{ đối tượng mục tiêu }}'],
  [/\{\{\s*([^}]+)\s*\}\}/g, '{{ $1 }}'],
];

function placeholders(s) {
  let out = s;
  for (const [re, rep] of ph) out = out.replace(re, rep);
  return out;
}

/** Longest-first phrase replacements for natural Vietnamese prompts */
const phrases = [
  ['Summarize the following:', 'Tóm tắt nội dung sau:'],
  ['Create a promotional ad for:', 'Tạo quảng cáo khuyến mãi cho:'],
  ['Create a social media post for:', 'Tạo bài đăng mạng xã hội cho:'],
  ['Write a witty Instagram caption about:', 'Viết caption Instagram dí dỏm về:'],
  ['Rewrite and improve the following content:', 'Viết lại và cải thiện nội dung sau:'],
  ['Write an engaging Facebook post about the company described below:', 'Viết bài Facebook hấp dẫn về doanh nghiệp mô tả bên dưới:'],
  ['Produce a Facebook status about the benefits of', 'Viết status Facebook về lợi ích của'],
  ['Write a Facebook post about', 'Viết bài Facebook về'],
  ['Give me an interesting question to post on my Facebook Group about', 'Gợi ý câu hỏi thú vị để đăng lên nhóm Facebook về'],
  ['Rephrase the following content as a catchy Facebook post.', 'Viết lại nội dung sau thành bài Facebook bắt mắt.'],
  ['Generate question ideas for a Facebook poll about', 'Gợi ý câu hỏi cho khảo sát Facebook về'],
  ['Craft a compelling story on how you can change your life for the better by using', 'Viết câu chuyện truyền cảm hứng về cách thay đổi cuộc sống tốt hơn nhờ'],
  ['Share a behind-the-scenes look at', 'Chia sẻ hậu trường tại'],
  ['and the hard work that goes into it.', 'và công sức đằng sau.'],
  ['Create a Facebook post that highlights the unique features of', 'Tạo bài Facebook làm nổi bật điểm đặc trưng của'],
  ['Write a Facebook status that educates your audience on the importance of', 'Viết status Facebook giúp đối tượng hiểu tầm quan trọng của'],
  ['Come up with a Facebook post that inspires your followers to take action regarding', 'Tạo bài Facebook truyền cảm hứng để follower hành động về'],
  ['Develop a thought-provoking Facebook post that sparks a conversation about', 'Soạn bài Facebook kích thích thảo luận về'],
  ['Design a Facebook quiz that challenges your followers', 'Thiết kế quiz Facebook thử thách follower'],
  ["followers\\\\\\' knowledge on", 'về kiến thức của follower về'],
  ["followers' knowledge on", 'về kiến thức của follower về'],
  ['Share a personal experience that impacted your life about', 'Chia sẻ trải nghiệm cá nhân ảnh hưởng đến cuộc sống bạn về'],
  ['Write a Facebook post that encourages your followers to share their opinions on', 'Viết bài Facebook khuyến khích follower chia sẻ ý kiến về'],
  ['Create a Facebook status that showcases', 'Tạo status Facebook giới thiệu'],
  ['Develop a Facebook post that asks for input on', 'Soạn bài Facebook xin ý kiến về'],
  ['Share a funny or relatable meme about', 'Chia sẻ meme vui hoặc dễ đồng cảm về'],
  ['Write a Facebook post that highlights the benefits of', 'Viết bài Facebook về lợi ích của'],
  ['Create a Facebook poll/quiz that challenges', 'Tạo poll/quiz Facebook thử thách'],
  ['Encourage my', 'Thuyết phục'],
  ['Establish trust and credibility with my', 'Xây dựng niềm tin với'],
  ['Persuade my', 'Thuyết phục'],
  ['Show how my', 'Cho thấy'],
  ['can meet', 'có thể đáp ứng'],
  ['Provide step-by-step guidance on how to', 'Hướng dẫn từng bước cách'],
  ['Write a post about', 'Viết bài đăng về'],
  ['Write a post that', 'Viết bài đăng'],
  ['Write a post offering', 'Viết bài đăng đưa ra'],
  ['Write a post outlining', 'Viết bài đăng phác thảo'],
  ['Write a post providing', 'Viết bài đăng chia sẻ'],
  ['Write a post sharing', 'Viết bài đăng chia sẻ'],
  ['Write a post announcing', 'Viết bài đăng thông báo'],
  ['Write a caption for', 'Viết chú thích cho'],
  ['Write a motivational message about', 'Viết lời nhắn truyền động lực về'],
  ['Create a post that', 'Tạo bài đăng'],
  ['Create a post featuring', 'Tạo bài đăng giới thiệu'],
  ['Create a post showcasing', 'Tạo bài đăng giới thiệu'],
  ['Create a post sharing', 'Tạo bài đăng chia sẻ'],
  ['Create a post announcing', 'Tạo bài đăng thông báo'],
  ['Share a post that', 'Chia sẻ bài đăng'],
  ['Share a post about', 'Chia sẻ bài đăng về'],
  ['Write an Instagram', 'Viết bài Instagram'],
  ['Write a LinkedIn', 'Viết bài LinkedIn'],
  ['Write a TikTok', 'Viết kịch bản TikTok'],
  ['Write a tweet', 'Viết tweet'],
  ['Write a Twitter', 'Viết tweet'],
  ['Write a Pinterest', 'Viết bài Pinterest'],
  ['Write a Youtube', 'Viết kịch bản Youtube'],
  ['Write a YouTube', 'Viết kịch bản YouTube'],
  ['Rewrite the following', 'Viết lại nội dung sau'],
  ['Rewrite this', 'Viết lại'],
  ['Summarize the following', 'Tóm tắt nội dung sau'],
  ['Summarize this', 'Tóm tắt'],
  ['Explain the following', 'Giải thích nội dung sau'],
  ['Expand on the following', 'Mở rộng nội dung sau'],
  ['Edit the following', 'Chỉnh sửa nội dung sau'],
  ['Improve the following', 'Cải thiện nội dung sau'],
  ['Generate', 'Tạo'],
  ['Develop', 'Xây dựng'],
  ['Design', 'Thiết kế'],
  ['Outline', 'Phác thảo'],
  ['Draft', 'Soạn thảo'],
  ['Compose', 'Soạn'],
  ['Suggest', 'Gợi ý'],
  ['Provide', 'Đưa ra'],
  ['Describe', 'Mô tả'],
  ['List', 'Liệt kê'],
  ['the benefits of', 'lợi ích của'],
  ['the importance of', 'tầm quan trọng của'],
  ['small businesses', 'hộ kinh doanh nhỏ'],
  ['small business', 'hộ kinh doanh nhỏ'],
  ['local business', 'cửa hàng địa phương'],
  ['marketing strategies', 'chiến lược marketing'],
  ['marketing strategy', 'chiến lược marketing'],
  ['customer feedback', 'phản hồi khách hàng'],
  ['social media', 'mạng xã hội'],
  ['followers', 'người theo dõi'],
  ['following content:', 'nội dung sau:'],
  ['following:', 'sau:'],
  ['about:', 'về:'],
  ['about the', 'về'],
  [' for ', ' cho '],
  [' with ', ' với '],
  [' and ', ' và '],
  [' on ', ' trên '],
  [' to ', ' để '],
  [' of ', ' của '],
  [' in ', ' trong '],
  [' your ', ' của bạn '],
  [' my ', ' của tôi '],
  [' our ', ' của chúng tôi '],
];

phrases.sort((a, b) => b[0].length - a[0].length);

function autoTranslate(en) {
  let out = placeholders(en.trim());
  for (const [from, to] of phrases) {
    if (out.includes(from)) out = out.split(from).join(to);
  }
  out = out.replace(/\s{2,}/g, ' ').trim();
  if (!out.endsWith('.') && !out.endsWith(':')) out += '.';
  return out;
}

const items = JSON.parse(fs.readFileSync(dataPath, 'utf8'));
const now = Math.floor(Date.now() / 1000);
let fromMap = 0;
let fromAuto = 0;

items.forEach((item, index) => {
  item.id = START_ID + index;
  item.category_name = categoryMap[item.category_name] ?? item.category_name;

  if (viMap[item.id_secure]) {
    item.content = viMap[item.id_secure];
    fromMap++;
  } else {
    item.content = autoTranslate(item.content);
    fromAuto++;
  }

  item.changed = now;
  item.created = now;
});

fs.writeFileSync(dataPath, `${JSON.stringify(items, null, 4)}\n`);

console.log(`Done ${items.length} templates, ids ${START_ID}-${START_ID + items.length - 1}`);
console.log(`From map: ${fromMap}, auto: ${fromAuto}`);

const sample = items.slice(0, 5).map((i) => i.content);
console.log('Samples:', sample);
