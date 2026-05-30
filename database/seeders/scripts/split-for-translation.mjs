import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = path.dirname(fileURLToPath(import.meta.url));
const items = JSON.parse(fs.readFileSync(path.join(dir, '../data/ai_templates.json'), 'utf8'));
const chunks = 4;
const size = Math.ceil(items.length / chunks);

for (let i = 0; i < chunks; i++) {
  const slice = items.slice(i * size, (i + 1) * size).map(({ id_secure, category_name, content }) => ({
    id_secure,
    category_name,
    content,
  }));
  fs.writeFileSync(
    path.join(dir, `../data/ai_templates_chunk_${i + 1}.json`),
    JSON.stringify(slice, null, 2),
  );
  console.log(`chunk ${i + 1}: ${slice.length}`);
}
