import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dataDir = path.join(__dirname, '../../../database/seeders/data');
const dataPath = path.join(dataDir, 'ai_templates.json');
const mapPath = path.join(dataDir, 'ai_templates_vi_map.json');
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

const placeholderMap = [
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
  [/\{\{\s*([^}]+)\s*\}\}/g, '{{ $1 }}'],
];

const exactMap = {
  'Summarize the following:': 'Tóm tắt nội dung sau:',
  'Create a promotional ad for:': 'Tạo quảng cáo khuyến mãi cho:',
  'Create a social media post for:': 'Tạo bài đăng mạng xã hội cho:',
  'Write a witty Instagram caption about:': 'Viết caption Instagram dí dỏm về:',
  'Rewrite and improve the following content:': 'Viết lại và cải thiện nội dung sau:',
  'Write an engaging Facebook post about the company described below:':
    'Viết bài Facebook hấp dẫn về doanh nghiệp mô tả bên dưới:',
  'Rephrase the following content as a catchy Facebook post.':
    'Viết lại nội dung sau thành bài Facebook bắt mắt.',
};

const sentencePatterns = [
  [/^Write a post about (.+)$/i, 'Viết bài đăng về $1'],
  [/^Write a caption for (.+)$/i, 'Viết chú thích cho $1'],
  [/^Write a motivational message about (.+)$/i, 'Viết lời nhắn truyền động lực về $1'],
  [/^Write an? (.+)$/i, 'Viết $1'],
  [/^Create a post that (.+)$/i, 'Tạo bài đăng $1'],
  [/^Create a post (.+)$/i, 'Tạo bài đăng $1'],
  [/^Create an? (.+)$/i, 'Tạo $1'],
  [/^Share a post that (.+)$/i, 'Chia sẻ bài đăng $1'],
  [/^Share a (.+)$/i, 'Chia sẻ $1'],
  [/^Generate (.+)$/i, 'Tạo $1'],
  [/^Produce (.+)$/i, 'Tạo $1'],
  [/^Give me (.+)$/i, 'Gợi ý $1'],
  [/^Craft (.+)$/i, 'Soạn $1'],
  [/^Rephrase (.+)$/i, 'Viết lại $1'],
  [/^Rewrite (.+)$/i, 'Viết lại $1'],
  [/^Explain (.+)$/i, 'Giải thích $1'],
  [/^Expand (.+)$/i, 'Mở rộng $1'],
  [/^Summarize (.+)$/i, 'Tóm tắt $1'],
  [/^Describe (.+)$/i, 'Mô tả $1'],
  [/^List (.+)$/i, 'Liệt kê $1'],
  [/^Suggest (.+)$/i, 'Gợi ý $1'],
  [/^Provide (.+)$/i, 'Đưa ra $1'],
  [/^Develop (.+)$/i, 'Xây dựng $1'],
  [/^Design (.+)$/i, 'Thiết kế $1'],
  [/^Outline (.+)$/i, 'Phác thảo $1'],
  [/^Draft (.+)$/i, 'Soạn thảo $1'],
  [/^Compose (.+)$/i, 'Soạn $1'],
];

const wordMap = new Map(
  Object.entries({
    the: '',
    a: '',
    an: '',
    and: 'và',
    or: 'hoặc',
    for: 'cho',
    with: 'với',
    about: 'về',
    on: 'trên',
    in: 'trong',
    to: 'để',
    of: 'của',
    that: 'mà',
    this: 'này',
    your: 'của bạn',
    our: 'của chúng tôi',
    my: 'của tôi',
    post: 'bài đăng',
    posts: 'bài đăng',
    content: 'nội dung',
    audience: 'đối tượng',
    customers: 'khách hàng',
    customer: 'khách hàng',
    business: 'doanh nghiệp',
    businesses: 'doanh nghiệp',
    marketing: 'marketing',
    brand: 'thương hiệu',
    product: 'sản phẩm',
    products: 'sản phẩm',
    service: 'dịch vụ',
    services: 'dịch vụ',
    benefits: 'lợi ích',
    benefit: 'lợi ích',
    importance: 'tầm quan trọng',
    tips: 'mẹo',
    tip: 'mẹo',
    ideas: 'ý tưởng',
    idea: 'ý tưởng',
    strategies: 'chiến lược',
    strategy: 'chiến lược',
    effective: 'hiệu quả',
    successful: 'thành công',
    engaging: 'hấp dẫn',
    compelling: 'thuyết phục',
    catchy: 'bắt mắt',
    interesting: 'thú vị',
    question: 'câu hỏi',
    questions: 'câu hỏi',
    poll: 'khảo sát',
    story: 'câu chuyện',
    storytelling: 'kể chuyện',
    feedback: 'phản hồi',
    offer: 'ưu đãi',
    announcement: 'thông báo',
    announcing: 'thông báo',
    highlights: 'làm nổi bật',
    highlight: 'làm nổi bật',
    showcasing: 'giới thiệu',
    showcase: 'giới thiệu',
    featuring: 'giới thiệu',
    feature: 'giới thiệu',
    offering: 'đưa ra',
    sharing: 'chia sẻ',
    providing: 'đưa ra',
    outlining: 'phác thảo',
    announcing: 'thông báo',
    small: 'nhỏ',
    local: 'địa phương',
    online: 'trực tuyến',
    presence: 'hiện diện',
    entrepreneurs: 'doanh nhân',
    entrepreneur: 'doanh nhân',
    owner: 'chủ',
    owners: 'chủ',
    team: 'đội ngũ',
    office: 'văn phòng',
    photo: 'ảnh',
    caption: 'chú thích',
    mistakes: 'sai lầm',
    mistake: 'sai lầm',
    role: 'vai trò',
    balancing: 'cân bằng',
    work: 'công việc',
    personal: 'cá nhân',
    life: 'cuộc sống',
    challenges: 'thử thách',
    challenge: 'thử thách',
    overcoming: 'vượt qua',
    qualities: 'phẩm chất',
    quality: 'phẩm chất',
    special: 'đặc biệt',
    coaching: 'coaching',
    time: 'thời gian',
    management: 'quản lý',
    motivational: 'truyền cảm hứng',
    quote: 'câu nói',
    cash: 'dòng tiền',
    flow: 'dòng tiền',
    webinar: 'webinar',
    building: 'xây dựng',
    scratch: 'từ con số không',
    strong: 'mạnh',
    following: 'sau',
    below: 'bên dưới',
    described: 'mô tả',
    company: 'công ty',
    described: 'mô tả',
    fitness: 'fitness',
    goals: 'mục tiêu',
    goal: 'mục tiêu',
    exercise: 'bài tập',
    routine: 'lịch tập',
    training: 'tập luyện',
    workout: 'buổi tập',
    healthy: 'healthy',
    recipe: 'công thức',
    smoothie: 'sinh tố',
    snack: 'món ăn vặt',
    recovery: 'phục hồi',
    favorite: 'yêu thích',
    incorporating: 'tích hợp',
    yoga: 'yoga',
    injury: 'chấn thương',
    technique: 'kỹ thuật',
    form: 'tư thế',
    proper: 'đúng',
    avoiding: 'tránh',
    patience: 'kiên nhẫn',
    persistence: 'bền bỉ',
    'self-belief': 'niềm tin bản thân',
    resistance: 'kháng lực',
    bands: 'dây',
    foam: 'foam',
    roller: 'con lăn',
    circuit: 'circuit',
    overall: 'tổng thể',
    core: 'core',
    stability: 'ổn định',
    strength: 'sức mạnh',
    chest: 'ngực',
    lower: 'dưới',
    body: 'cơ thể',
    mid-day: 'giữa ngày',
    energy: 'năng lượng',
    boost: 'tăng lực',
    pre-workout: 'trước tập',
    post-workout: 'sau tập',
    meal: 'bữa ăn',
    prep: 'chuẩn bị',
    busy: 'bận rộn',
    week: 'tuần',
    behind-the-scenes: 'hậu trường',
    hard: 'khó khăn',
    goes: 'bỏ ra',
    into: 'vào',
    it: 'đó',
    change: 'thay đổi',
    better: 'tốt hơn',
    using: 'sử dụng',
    change: 'thay đổi',
    life: 'cuộc sống',
    how: 'cách',
    you: 'bạn',
    can: 'có thể',
    your: 'của bạn',
    by: 'bằng',
    at: 'tại',
    look: 'góc nhìn',
    Group: 'Nhóm',
    Facebook: 'Facebook',
    status: 'status',
    Produce: 'Tạo',
    status: 'status',
    poll: 'khảo sát',
    rephrase: 'viết lại',
    witty: 'dí dỏm',
    Instagram: 'Instagram',
    promotional: 'khuyến mãi',
    ad: 'quảng cáo',
    social: 'mạng xã hội',
    media: '',
    improve: 'cải thiện',
    following: 'sau',
  }).sort((a, b) => b[0].length - a[0].length),
);

function applyPlaceholders(text) {
  let out = text;
  for (const [re, rep] of placeholderMap) {
    out = out.replace(re, rep);
  }
  return out;
}

function translateFragment(text) {
  let out = text.trim();
  if (exactMap[out]) {
    return exactMap[out];
  }

  const trimmed = out.replace(/\.$/, '');
  for (const [re, rep] of sentencePatterns) {
    const m = trimmed.match(re);
    if (m) {
      out = rep.replace('$1', m[1] ?? '');
      break;
    }
  }

  if (/[A-Za-z]{4,}/.test(out)) {
    const tokens = out.split(/(\s+|[^\w\s{{}}]+)/);
    out = tokens
      .map((token) => {
        const key = token.toLowerCase();
        if (wordMap.has(key)) {
          const v = wordMap.get(key);
          return v === '' ? '' : v;
        }
        return token;
      })
      .join('')
      .replace(/\s{2,}/g, ' ')
      .replace(/\s+([,.])/g, '$1')
      .trim();
  }

  out = out.replace(/\s{2,}/g, ' ').trim();
  if (out && !/[.!?]$/.test(out) && /[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i.test(out)) {
    out += '.';
  }

  return out;
}

function translateContent(en) {
  let out = applyPlaceholders(en);
  if (exactMap[out]) {
    return exactMap[out];
  }
  out = translateFragment(out);
  if (/[A-Za-z]{5,}/.test(out)) {
    out = `${out} (Viết bằng tiếng Việt, phù hợp thị trường Việt Nam.)`;
  }
  return out;
}

const items = JSON.parse(fs.readFileSync(dataPath, 'utf8'));
const viMap = fs.existsSync(mapPath) ? JSON.parse(fs.readFileSync(mapPath, 'utf8')) : {};
const now = Math.floor(Date.now() / 1000);

items.forEach((item, index) => {
  item.id = START_ID + index;
  const cat = item.category_name;
  item.category_name = categoryMap[cat] ?? cat;

  const key = item.id_secure;
  item.content = viMap[key] ?? translateContent(item.content);

  item.changed = now;
  item.created = now;
});

fs.writeFileSync(dataPath, `${JSON.stringify(items, null, 4)}\n`, 'utf8');

const remaining = items.filter((i) => /[A-Za-z]{6,}/.test(i.content) && !i.content.includes('(Viết bằng tiếng Việt')).length;
console.log(`Wrote ${items.length} templates. IDs ${START_ID}..${START_ID + items.length - 1}`);
console.log(`Entries still with long English fragments: ${remaining}`);
