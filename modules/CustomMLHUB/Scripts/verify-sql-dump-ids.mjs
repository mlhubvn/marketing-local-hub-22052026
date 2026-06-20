import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const repoRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../..');
const sql = fs.readFileSync(path.join(repoRoot, 'mysql-dump-default-1780902564.sql'), 'utf8');
const START = 147123468;
const bad = [];
const insertRe = /INSERT INTO `([^`]+)` VALUES ([\s\S]*?);\n/g;
let match;

while ((match = insertRe.exec(sql)) !== null) {
    const table = match[1];

    const rowRe = /\((\d+),/g;
    let rowMatch;

    while ((rowMatch = rowRe.exec(match[2])) !== null) {
        const id = Number(rowMatch[1]);

        if (id < START) {
            bad.push({ table, id });
        }
    }
}

console.log('IDs below START:', bad.length);

if (bad.length > 0) {
    console.log(bad.slice(0, 30));
    process.exit(1);
}

const optionsMatch = sql.match(/INSERT INTO `options` VALUES ([\s\S]*?);\n/);

if (optionsMatch) {
    const ids = [...optionsMatch[1].matchAll(/\((\d+),/g)].map((x) => Number(x[1]));
    const unique = new Set(ids);

    console.log(
        `options: ${ids.length} rows, min=${Math.min(...ids)}, max=${Math.max(...ids)}, duplicates=${ids.length - unique.size}`,
    );
}

console.log('OK');
