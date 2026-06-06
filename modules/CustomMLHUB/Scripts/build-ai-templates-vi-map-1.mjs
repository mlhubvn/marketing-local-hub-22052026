import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dataDir = path.join(__dirname, '../../../database/seeders/data');
const chunkPath = path.join(dataDir, 'ai_templates_chunk_1.json');
const outPath = path.join(dataDir, 'ai_templates_vi_map_1.json');

/** Normalize escaped apostrophes from seed JSON */
function norm(s) {
  return s.replace(/\\+'/g, "'").replace(/\s+/g, ' ').trim();
}

const placeholderRules = [
  [/\{\{\s*ideal customer persona\s*\}\}/gi, '{{ khách hàng lý tưởng }}'],
  [/\{\{\s*desired action\s*\}\}/gi, '{{ hành động mong muốn }}'],
  [/\{\{\s*company\/brand\s*\}\}/gi, '{{ công ty/thương hiệu }}'],
  [/\{\{\s*website\/product\s*\}\}/gi, '{{ website/sản phẩm }}'],
  [/\{\{\s*topic\/event\/product\/service\s*\}\}/gi, '{{ chủ đề/sự kiện/sản phẩm/dịch vụ }}'],
  [/\{\{\s*topic\/product\/service\s*\}\}/gi, '{{ chủ đề/sản phẩm/dịch vụ }}'],
  [/\{\{\s*product\/service\s*\}\}/gi, '{{ sản phẩm/dịch vụ }}'],
  [/\{\{\s*company\/project\s*\}\}/gi, '{{ công ty/dự án }}'],
  [/\{\{\s*industry\/profession\s*\}\}/gi, '{{ ngành/nghề nghiệp }}'],
  [/\{\{\s*industry\/niche\s*\}\}/gi, '{{ ngành/ngách }}'],
  [/\{\{\s*event\/product launch\/company news\s*\}\}/gi, '{{ sự kiện/ra mắt sản phẩm/tin công ty }}'],
  [/\{\{\s*partnership\/collaboration\s*\}\}/gi, '{{ hợp tác/liên kết }}'],
  [/\{\{\s*award\/recognition\s*\}\}/gi, '{{ giải thưởng/sự công nhận }}'],
  [/\{\{\s*discount\/offer\s*\}\}/gi, '{{ giảm giá/ưu đãi }}'],
  [/\{\{\s*common concern\s*\}\}/gi, '{{ thắc mắc thường gặp }}'],
  [/\{\{\s*product or service\s*\}\}/gi, '{{ sản phẩm hoặc dịch vụ }}'],
  [/\{\{\s*photo description\s*\}\}/gi, '{{ mô tả ảnh }}'],
  [/\{\{\s*tone of voice\s*\}\}/gi, '{{ giọng điệu }}'],
  [/\{\{\s*video title\s*\}\}/gi, '{{ tiêu đề video }}'],
  [/\{\{\s*target audience\s*\}\}/gi, '{{ đối tượng mục tiêu }}'],
  [/\{\{\s*business type\s*\}\}/gi, '{{ loại hình kinh doanh }}'],
  [/\{\{\s*individual\/profession\s*\}\}/gi, '{{ cá nhân/nghề nghiệp }}'],
  [/\{\{\s*specific audience\s*\}\}/gi, '{{ đối tượng cụ thể }}'],
  [/\{\{\s*character limit\s*\}\}/gi, '{{ giới hạn ký tự }}'],
  [/\{\{\s*angle\/subtopic\s*\}\}/gi, '{{ góc nhìn/chủ đề phụ }}'],
  [/\{\{\s*related topic\s*\}\}/gi, '{{ chủ đề liên quan }}'],
  [/\{\{\s*concept\/idea\s*\}\}/gi, '{{ khái niệm/ý tưởng }}'],
  [/\{\{\s*event\/situation\s*\}\}/gi, '{{ sự kiện/tình huống }}'],
  [/\{\{\s*event\/development\s*\}\}/gi, '{{ sự kiện/diễn biến }}'],
  [/\{\{\s*field\/industry\s*\}\}/gi, '{{ lĩnh vực/ngành }}'],
  [/\{\{\s*problem\/success\s*\}\}/gi, '{{ vấn đề/thành công }}'],
  [/\{\{\s*action\/decision\s*\}\}/gi, '{{ hành động/quyết định }}'],
  [/\{\{\s*task\/action\s*\}\}/gi, '{{ nhiệm vụ/hành động }}'],
  [/\{\{\s*tool\/application\s*\}\}/gi, '{{ công cụ/ứng dụng }}'],
  [/\{\{\s*product name\s*\}\}/gi, '{{ tên sản phẩm }}'],
  [/\{\{\s*new service\s*\}\}/gi, '{{ dịch vụ mới }}'],
  [/\{\{\s*topic\s*\}\}/gi, '{{ chủ đề }}'],
  [/\{\{\s*subject\s*\}\}/gi, '{{ chủ đề }}'],
  [/\{\{\s*product\s*\}\}/gi, '{{ sản phẩm }}'],
  [/\{\{\s*service\s*\}\}/gi, '{{ dịch vụ }}'],
  [/\{\{\s*company\s*\}\}/gi, '{{ công ty }}'],
  [/\{\{\s*brand\s*\}\}/gi, '{{ thương hiệu }}'],
  [/\{\{\s*audience\s*\}\}/gi, '{{ đối tượng }}'],
  [/\{\{\s*website\s*\}\}/gi, '{{ website }}'],
  [/\{\{\s*action\s*\}\}/gi, '{{ hành động }}'],
  [/\{\{\s*issue\s*\}\}/gi, '{{ vấn đề }}'],
  [/\{\{\s*goal\s*\}\}/gi, '{{ mục tiêu }}'],
  [/\{\{\s*achievement\s*\}\}/gi, '{{ thành tựu }}'],
  [/\{\{\s*adjective\s*\}\}/gi, '{{ tính từ }}'],
  [/\{\{\s*genre\s*\}\}/gi, '{{ thể loại }}'],
  [/\{\{\s*angle\s*\}\}/gi, '{{ góc nhìn }}'],
  [/\{\{\s*values\s*\}\}/gi, '{{ giá trị }}'],
  [/\{\{\s*benefit\s*\}\}/gi, '{{ lợi ích }}'],
  [/\{\{\s*need\s*\}\}/gi, '{{ nhu cầu }}'],
  [/\{\{\s*problem\s*\}\}/gi, '{{ vấn đề }}'],
  [/\{\{\s*factor\s*\}\}/gi, '{{ yếu tố }}'],
  [/\{\{\s*trend\s*\}\}/gi, '{{ xu hướng }}'],
];

function applyPlaceholders(text) {
  let out = text;
  for (const [re, rep] of placeholderRules) {
    out = out.replace(re, rep);
  }
  return out;
}

/** Exact English → Vietnamese (normalized keys) */
const exactVi = new Map(
  Object.entries({
    'Summarize the following:': 'Tóm tắt nội dung sau:',
    'Create a promotional ad for:': 'Tạo quảng cáo khuyến mãi cho:',
    'Create a social media post for:': 'Tạo bài đăng mạng xã hội cho:',
    'Write a witty Instagram caption about:': 'Viết caption Instagram dí dỏm về:',
    'Rewrite and improve the following content:': 'Viết lại và cải thiện nội dung sau:',
    'Write an engaging Facebook post about the company described below:':
      'Viết bài Facebook hấp dẫn về doanh nghiệp mô tả bên dưới:',
    'Produce a Facebook status about the benefits of {{ topic }}.':
      'Viết status Facebook về lợi ích của {{ chủ đề }}.',
    'Write a Facebook post about {{ topic }}.': 'Viết bài Facebook về {{ chủ đề }}.',
    'Give me an interesting question to post on my Facebook Group about {{ topic }}.':
      'Gợi ý câu hỏi thú vị để đăng lên nhóm Facebook về {{ chủ đề }}.',
    'Rephrase the following content as a catchy Facebook post.':
      'Viết lại nội dung sau thành bài Facebook bắt mắt.',
    'Generate question ideas for a Facebook poll about {{ topic }}.':
      'Gợi ý câu hỏi cho khảo sát Facebook về {{ chủ đề }}.',
    'Craft a compelling story on how you can change your life for the better by using {{ product/service }}.':
      'Viết câu chuyện truyền cảm hứng về cách thay đổi cuộc sống tốt hơn nhờ {{ sản phẩm/dịch vụ }}.',
    'Share a behind-the-scenes look at {{ company/project }} and the hard work that goes into it.':
      'Chia sẻ hậu trường tại {{ công ty/dự án }} và công sức đằng sau đó.',
    'Create a Facebook post that highlights the unique features of {{ product/service }}.':
      'Tạo bài Facebook làm nổi bật điểm đặc biệt của {{ sản phẩm/dịch vụ }}.',
    'Write a Facebook status that educates your audience on the importance of {{ topic }}.':
      'Viết status Facebook giúp đối tượng hiểu tầm quan trọng của {{ chủ đề }}.',
    'Come up with a Facebook post that inspires your followers to take action regarding {{ issue }}.':
      'Gợi ý bài Facebook truyền cảm hứng để người theo dõi hành động về {{ vấn đề }}.',
    'Develop a thought-provoking Facebook post that sparks a conversation about {{ topic }}.':
      'Xây dựng bài Facebook kích thích tư duy, mở cuộc trò chuyện về {{ chủ đề }}.',
    "Design a Facebook quiz that challenges your followers' knowledge on {{ topic }}.":
      'Thiết kế quiz Facebook thử thách kiến thức người theo dõi về {{ chủ đề }}.',
    'Share a personal experience that impacted your life about {{ topic }}.':
      'Chia sẻ trải nghiệm cá nhân đã tác động đến cuộc sống bạn về {{ chủ đề }}.',
    'Write a Facebook post that encourages your followers to share their opinions on {{ topic }}.':
      'Viết bài Facebook khuyến khích người theo dõi chia sẻ ý kiến về {{ chủ đề }}.',
    "Create a Facebook status that showcases how today's society is impacted by {{ product/service }}.":
      'Tạo status Facebook cho thấy xã hội hôm nay chịu ảnh hưởng thế nào từ {{ sản phẩm/dịch vụ }}.',
    'Develop a Facebook post that asks for improvement feedback on {{ company/project }}.':
      'Xây dựng bài Facebook xin phản hồi cải thiện về {{ công ty/dự án }}.',
    'Share a funny or relatable meme about {{ topic }}.':
      'Chia sẻ meme vui hoặc dễ đồng cảm về {{ chủ đề }}.',
    'Write a Facebook post that highlights the success stories of those who have used {{ product/service }}.':
      'Viết bài Facebook kể câu chuyện thành công của người đã dùng {{ sản phẩm/dịch vụ }}.',
    'Create a Facebook poll/quiz that challenges your followers to think critically about {{ topic }}.':
      'Tạo khảo sát/quiz Facebook giúp người theo dõi suy nghĩ sâu về {{ chủ đề }}.',
  }).map(([k, v]) => [norm(k), v]),
);

// Load extended translations from companion module
const { extendedExactVi } = await import('./vi-map-chunk1-translations.mjs');

for (const [k, v] of extendedExactVi) {
  exactVi.set(norm(k), typeof v === 'string' ? v : v);
}

function translate(en) {
  const key = norm(en);
  if (exactVi.has(key)) {
    return exactVi.get(key);
  }
  let out = applyPlaceholders(key);
  if (exactVi.has(out)) {
    return exactVi.get(out);
  }
  throw new Error(`Missing translation: ${key.slice(0, 120)}...`);
}

const chunk = JSON.parse(fs.readFileSync(chunkPath, 'utf8'));
const map = {};

for (const item of chunk) {
  map[item.id_secure] = translate(item.content);
}

fs.writeFileSync(outPath, `${JSON.stringify(map, null, 2)}\n`, 'utf8');

const enLeft = Object.values(map).filter((v) => /\b(the|and|with|your|about|Create a|Write a|Generate)\b/i.test(v));
console.log(`Wrote ${Object.keys(map).length} entries to ${outPath}`);
if (enLeft.length) {
  console.warn(`Possible English remnants: ${enLeft.length}`);
  enLeft.slice(0, 5).forEach((s) => console.warn(s));
}
