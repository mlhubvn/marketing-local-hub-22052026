import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, '../../..');
const dumpPath = path.join(repoRoot, 'mysql-dump-default-1780902564.sql');
const START = 147123468;
const EXCLUDED_TABLES = new Set([]);

const sql = fs.readFileSync(dumpPath, 'utf8');

/** @type {Map<string, Map<number, number>>} */
const tableMaps = new Map();

function buildSequentialMap(ids) {
    const sorted = [...new Set(ids)].sort((a, b) => a - b);
    const map = new Map();

    sorted.forEach((id, index) => {
        map.set(id, START + index);
    });

    return map;
}

function collectPrimaryKeys() {
    const insertRe = /INSERT INTO `([^`]+)` VALUES ([\s\S]*?);\n/g;
    let match;

    while ((match = insertRe.exec(sql)) !== null) {
        const table = match[1];

        if (EXCLUDED_TABLES.has(table)) {
            continue;
        }

        const body = match[2];
        const ids = [];
        const rowRe = /\((\d+),/g;
        let rowMatch;

        while ((rowMatch = rowRe.exec(body)) !== null) {
            ids.push(Number(rowMatch[1]));
        }

        if (ids.length === 0) {
            continue;
        }

        const hasLegacy = ids.some((id) => id < START);

        if (!hasLegacy) {
            continue;
        }

        tableMaps.set(table, buildSequentialMap(ids));
    }
}

function remapInsertPrimaryKeys(content) {
    let output = content;

    for (const [table, map] of tableMaps.entries()) {
        const insertRe = new RegExp(
            `(INSERT INTO \`${table}\` VALUES )([\\s\\S]*?)(;\\n)`,
            'g',
        );

        output = output.replace(insertRe, (full, prefix, body, suffix) => {
            let newBody = body;

            for (const [oldId, newId] of [...map.entries()].sort(
                (a, b) => b[0] - a[0],
            )) {
                newBody = newBody.replace(
                    new RegExp(`\\(${oldId},`, 'g'),
                    `(${newId},`,
                );
            }

            return prefix + newBody + suffix;
        });
    }

    return output;
}

function remapForeignKeyColumn(content, table, columnIndex, refTable) {
    const refMap = tableMaps.get(refTable);

    if (!refMap || refMap.size === 0) {
        return content;
    }

    const insertRe = new RegExp(
        `(INSERT INTO \`${table}\` VALUES )([\\s\\S]*?)(;\\n)`,
        'g',
    );

    return content.replace(insertRe, (full, prefix, body, suffix) => {
        const rows = splitRows(body);
        const newRows = rows.map((row) => {
            const fields = splitFields(row);

            if (fields.length < columnIndex) {
                return row;
            }

            const raw = fields[columnIndex - 1].trim();
            const legacyId = Number(raw);

            if (!Number.isInteger(legacyId) || !refMap.has(legacyId)) {
                return row;
            }

            fields[columnIndex - 1] = String(refMap.get(legacyId));
            return `(${fields.join(',')})`;
        });

        return prefix + newRows.join(',') + suffix;
    });
}

function splitRows(body) {
    const rows = [];
    let current = '';
    let depth = 0;
    let inString = false;
    let escape = false;

    for (let i = 0; i < body.length; i++) {
        const ch = body[i];
        current += ch;

        if (escape) {
            escape = false;
            continue;
        }

        if (ch === '\\') {
            escape = true;
            continue;
        }

        if (ch === "'" && !escape) {
            inString = !inString;
            continue;
        }

        if (inString) {
            continue;
        }

        if (ch === '(') {
            depth++;
            continue;
        }

        if (ch === ')') {
            depth--;

            if (depth === 0) {
                rows.push(current.trim().replace(/^\(/, '').replace(/\)$/, ''));
                current = '';
                if (body[i + 1] === ',') {
                    i++;
                }
            }
        }
    }

    return rows;
}

function splitFields(row) {
    const fields = [];
    let current = '';
    let inString = false;
    let escape = false;

    for (let i = 0; i < row.length; i++) {
        const ch = row[i];

        if (escape) {
            current += ch;
            escape = false;
            continue;
        }

        if (ch === '\\') {
            current += ch;
            escape = true;
            continue;
        }

        if (ch === "'") {
            inString = !inString;
            current += ch;
            continue;
        }

        if (ch === ',' && !inString) {
            fields.push(current);
            current = '';
            continue;
        }

        current += ch;
    }

    if (current !== '') {
        fields.push(current);
    }

    return fields;
}

function collectMaxPrimaryKeys(content) {
    /** @type {Map<string, number>} */
    const maxByTable = new Map();
    const insertRe = /INSERT INTO `([^`]+)` VALUES ([\s\S]*?);\n/g;
    let match;

    while ((match = insertRe.exec(content)) !== null) {
        const table = match[1];

        if (EXCLUDED_TABLES.has(table)) {
            continue;
        }

        const rowRe = /\((\d+),/g;
        let rowMatch;
        let maxId = maxByTable.get(table) ?? 0;

        while ((rowMatch = rowRe.exec(match[2])) !== null) {
            maxId = Math.max(maxId, Number(rowMatch[1]));
        }

        if (maxId > 0) {
            maxByTable.set(table, maxId);
        }
    }

    return maxByTable;
}

function remapAutoIncrement(content) {
    const maxByTable = collectMaxPrimaryKeys(content);

    return content.replace(
        /CREATE TABLE `([^`]+)` \(([\s\S]*?)\) ENGINE=InnoDB AUTO_INCREMENT=(\d+)/g,
        (full, table, body, current) => {
            const maxId = maxByTable.get(table) ?? 0;
            const next = Math.max(START, maxId > 0 ? maxId + 1 : START);

            return `CREATE TABLE \`${table}\` (${body}) ENGINE=InnoDB AUTO_INCREMENT=${next}`;
        },
    );
}

collectPrimaryKeys();

let output = sql;
output = remapInsertPrimaryKeys(output);
output = remapForeignKeyColumn(output, 'team_user', 2, 'teams');
output = remapForeignKeyColumn(output, 'lb_email_automations', 4, 'lb_email_templates');
output = remapForeignKeyColumn(output, 'lb_email_automation_logs', 4, 'lb_email_templates');
output = remapForeignKeyColumn(output, 'lb_template_pack_items', 2, 'lb_template_packs');
output = remapAutoIncrement(output);

fs.writeFileSync(dumpPath, output, 'utf8');

console.log('MLHUB SQL dump ID remap complete.');
console.log('Tables updated:');

for (const [table, map] of [...tableMaps.entries()].sort()) {
    const legacy = [...map.entries()].filter(([oldId]) => oldId < START);

    if (legacy.length === 0) {
        continue;
    }

    console.log(
        `  ${table}: ${legacy.length} legacy id(s) -> ${legacy
            .map(([oldId, newId]) => `${oldId}->${newId}`)
            .join(', ')}`,
    );
}
