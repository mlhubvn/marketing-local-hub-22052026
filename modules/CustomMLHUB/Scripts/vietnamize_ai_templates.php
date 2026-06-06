<?php

/**
 * One-off: Việt hóa ai_templates.json + remap id >= 147123468.
 * Run: php modules/CustomMLHUB/Scripts/vietnamize_ai_templates.php
 */

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$dataPath = $basePath.'/database/seeders/data/ai_templates.json';
$startingId = 147123468;

$categoryMap = [
    'Suggested' => 'Gợi ý',
    'Facebook' => 'Facebook',
    'Instagram' => 'Instagram',
    'X (Twitter)' => 'X (Twitter)',
    'LinkedIn' => 'LinkedIn',
    'Pinterest' => 'Pinterest',
    'Google Business Profile' => 'Google Business Profile',
    'TikTok' => 'TikTok',
    'Youtube' => 'Youtube',
    'Rewrite' => 'Viết lại',
    'Edit' => 'Chỉnh sửa',
    'Explain & Expand' => 'Giải thích & Mở rộng',
    'Summarize' => 'Tóm tắt',
    'Psychological Frameworks' => 'Khung tâm lý',
    'Content Creation Frameworks' => 'Khung sáng tạo nội dung',
    'Ads' => 'Quảng cáo',
    'Company-Related' => 'Doanh nghiệp',
    'CTA Prompts' => 'Kêu gọi hành động (CTA)',
    'Educational' => 'Giáo dục',
    'Fun Prompts' => 'Vui vẻ',
    'Interactive' => 'Tương tác',
    'Inspirational' => 'Truyền cảm hứng',
    'Holiday' => 'Ngày lễ',
    'Small Businesses' => 'Hộ kinh doanh nhỏ',
    'Business Coaches' => 'Huấn luyện kinh doanh',
    'Lifestyle Coaches' => 'Huấn luyện lối sống',
    'Realtors' => 'Môi giới bất động sản',
    'NGOs' => 'Tổ chức phi lợi nhuận',
    'Entreprenuers' => 'Doanh nhân',
    'Marketing Agencies' => 'Agency marketing',
    'Authors' => 'Tác giả',
    'Accounting Firms' => 'Công ty kế toán',
    'Restaurants' => 'Nhà hàng',
    'Fitness Trainers' => 'Huấn luyện viên fitness',
];

$phraseMap = [
    '{{ topic }}' => '{{ chủ đề }}',
    '{{ product/service }}' => '{{ sản phẩm/dịch vụ }}',
    '{{ company/project }}' => '{{ công ty/dự án }}',
    '{{ product }}' => '{{ sản phẩm }}',
    '{{ service }}' => '{{ dịch vụ }}',
    '{{ company }}' => '{{ công ty }}',
    '{{ brand }}' => '{{ thương hiệu }}',
    '{{ audience }}' => '{{ đối tượng }}',
    '{{ niche }}' => '{{ ngành hàng }}',
    '{{ event }}' => '{{ sự kiện }}',
    '{{ holiday }}' => '{{ ngày lễ }}',
    '{{ goal }}' => '{{ mục tiêu }}',
    '{{ industry }}' => '{{ ngành }}',
    '{{ location }}' => '{{ địa điểm }}',
    '{{ name }}' => '{{ tên }}',
    '{{ business }}' => '{{ doanh nghiệp }}',
    '{{ offer }}' => '{{ ưu đãi }}',
    '{{ product name }}' => '{{ tên sản phẩm }}',
];

$prefixMap = [
    'Summarize the following:' => 'Tóm tắt nội dung sau:',
    'Create a promotional ad for:' => 'Tạo quảng cáo khuyến mãi cho:',
    'Create a social media post for:' => 'Tạo bài đăng mạng xã hội cho:',
    'Write a witty Instagram caption about:' => 'Viết caption Instagram dí dỏm về:',
    'Rewrite and improve the following content:' => 'Viết lại và cải thiện nội dung sau:',
    'Write an engaging Facebook post about the company described below:' => 'Viết bài Facebook hấp dẫn về doanh nghiệp mô tả bên dưới:',
    'Produce a Facebook status about the benefits of' => 'Viết status Facebook về lợi ích của',
    'Write a Facebook post about' => 'Viết bài Facebook về',
    'Give me an interesting question to post on my Facebook Group about' => 'Gợi ý câu hỏi thú vị để đăng lên nhóm Facebook về',
    'Rephrase the following content as a catchy Facebook post.' => 'Viết lại nội dung sau thành bài Facebook bắt mắt.',
    'Generate question ideas for a Facebook poll about' => 'Gợi ý câu hỏi cho poll Facebook về',
    'Craft a compelling story on how you can change your life for the better by using' => 'Viết câu chuyện truyền cảm hứng về cách thay đổi cuộc sống nhờ',
    'Share a behind-the-scenes look at' => 'Chia sẻ hậu trường tại',
    'and the hard work that goes into it.' => 'và công sức đằng sau đó.',
    'Write a' => 'Viết',
    'Create a' => 'Tạo',
    'Generate' => 'Tạo',
    'Produce a' => 'Tạo',
    'Give me' => 'Gợi ý cho tôi',
    'Craft a' => 'Soạn',
    'Share a' => 'Chia sẻ',
    'Rephrase the' => 'Viết lại',
    'Rewrite the' => 'Viết lại',
    'Explain the' => 'Giải thích',
    'Expand on the' => 'Mở rộng',
    'Summarize the' => 'Tóm tắt',
    'Describe the' => 'Mô tả',
    'List' => 'Liệt kê',
    'Suggest' => 'Gợi ý',
    'Provide' => 'Đưa ra',
    'Develop' => 'Xây dựng',
    'Design' => 'Thiết kế',
    'Outline' => 'Phác thảo',
    'Draft' => 'Soạn thảo',
    'Compose' => 'Soạn',
    'post about' => 'bài đăng về',
    'post featuring' => 'bài đăng giới thiệu',
    'post showcasing' => 'bài đăng giới thiệu',
    'motivational message about' => 'lời nhắn truyền động lực về',
    'the benefits of' => 'lợi ích của',
    'the importance of' => 'tầm quan trọng của',
    'for post-workout recovery.' => 'để phục hồi sau tập.',
    'for a busy week.' => 'cho tuần bận rộn.',
    'for mid-day energy.' => 'cho năng lượng giữa ngày.',
    'for a pre-workout boost.' => 'trước khi tập.',
    'healthy smoothie recipe' => 'công thức sinh tố healthy',
    'healthy snack recipe' => 'công thức món ăn vặt healthy',
    'healthy meal prep recipe' => 'công thức meal prep healthy',
    'favorite exercise for' => 'bài tập yêu thích để',
    'building core stability' => 'tăng ổn định cơ core',
    'building strength in the chest' => 'tăng sức mạnh ngực',
    'building lower body strength' => 'tăng sức mạnh chân',
    'incorporating' => 'tích hợp',
    'into a fitness routine' => 'vào lịch tập',
    'during exercise' => 'khi tập luyện',
    'fitness goals' => 'mục tiêu fitness',
    'achieving fitness goals' => 'đạt mục tiêu fitness',
    'realistic and achievable' => 'thực tế và khả thi',
    'circuit training' => 'tập circuit',
    'overall fitness' => 'thể lực tổng thể',
    'foam roller' => 'con lăn foam',
    'myofascial release' => 'giải phóng cơ myofascial',
    'resistance bands' => 'dây kháng lực',
    'proper form and technique' => 'tư thế và kỹ thuật đúng',
    'avoiding injury' => 'tránh chấn thương',
    'patience and persistence' => 'kiên nhẫn và bền bỉ',
    'self-belief' => 'niềm tin vào bản thân',
    'following content:' => 'nội dung sau:',
    'following:' => 'sau:',
    'about:' => 'về:',
    'about the' => 'về',
    ' on ' => ' trên ',
    ' for ' => ' cho ',
    ' with ' => ' với ',
    ' and ' => ' và ',
    ' your ' => ' của bạn ',
    ' my ' => ' của tôi ',
    ' our ' => ' của chúng tôi ',
    ' customers ' => ' khách hàng ',
    ' audience ' => ' đối tượng ',
    ' business ' => ' doanh nghiệp ',
    ' brand ' => ' thương hiệu ',
    ' product ' => ' sản phẩm ',
    ' service ' => ' dịch vụ ',
    ' marketing ' => ' marketing ',
    ' social media ' => ' mạng xã hội ',
    ' small business ' => ' hộ kinh doanh nhỏ ',
    ' local business ' => ' cửa hàng địa phương ',
    ' Vietnamese ' => ' Việt Nam ',
    ' Vietnam ' => ' Việt Nam ',
];

function translateContent(string $content, array $phraseMap, array $prefixMap): string
{
  $out = $content;

  foreach ($phraseMap as $en => $vi) {
    $out = str_replace($en, $vi, $out);
  }

  uksort($prefixMap, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

  foreach ($prefixMap as $en => $vi) {
    if (str_contains($out, $en)) {
      $out = str_replace($en, $vi, $out);
    }
  }

  if (preg_match('/\b(the|a|an|to|of|in|on|for|with|that|this|your|our|my)\b/i', $out)) {
    $out = preg_replace('/\bWrite\b/i', 'Viết', $out) ?? $out;
    $out = preg_replace('/\bCreate\b/i', 'Tạo', $out) ?? $out;
    $out = preg_replace('/\bGenerate\b/i', 'Tạo', $out) ?? $out;
    $out = preg_replace('/\bShare\b/i', 'Chia sẻ', $out) ?? $out;
    $out = preg_replace('/\bExplain\b/i', 'Giải thích', $out) ?? $out;
    $out = preg_replace('/\bDescribe\b/i', 'Mô tả', $out) ?? $out;
    $out = preg_replace('/\bSuggest\b/i', 'Gợi ý', $out) ?? $out;
    $out = preg_replace('/\babout\b/i', 'về', $out) ?? $out;
    $out = preg_replace('/\bfor\b/i', 'cho', $out) ?? $out;
    $out = preg_replace('/\bwith\b/i', 'với', $out) ?? $out;
    $out = preg_replace('/\bthe\b/i', '', $out) ?? $out;
    $out = preg_replace('/\s{2,}/', ' ', $out) ?? $out;
    $out = trim($out);
  }

  return $out;
}

$raw = file_get_contents($dataPath);
if ($raw === false) {
  fwrite(STDERR, "Cannot read {$dataPath}\n");
  exit(1);
}

$items = json_decode($raw, true);
if (! is_array($items)) {
  fwrite(STDERR, "Invalid JSON\n");
  exit(1);
}

$now = time();
$index = 0;

foreach ($items as &$item) {
  if (! is_array($item)) {
    continue;
  }

  $legacy = (int) ($item['id'] ?? ($index + 1));
  $item['id'] = $startingId + $index;
  $index++;

  $cat = (string) ($item['category_name'] ?? '');
  $item['category_name'] = $categoryMap[$cat] ?? $cat;

  $content = (string) ($item['content'] ?? '');
  $item['content'] = translateContent($content, $phraseMap, $prefixMap);

  $item['changed'] = $now;
  $item['created'] = $now;
}
unset($item);

$encoded = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($encoded === false) {
  fwrite(STDERR, "Encode failed\n");
  exit(1);
}

file_put_contents($dataPath, $encoded."\n");

echo "Done: {$index} templates, ids {$startingId}..".($startingId + $index - 1)."\n";
