import sharp from 'sharp';
import pngToIco from 'png-to-ico';
import { readFileSync, writeFileSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');


// apple-touch-icon (180x180 per Apple guidance)
writeFileSync(resolve(root, 'public/apple-touch-icon.png'), await png(180));

// favicon.ico — multi-size for crisp rendering at all scales
const ico = await pngToIco([await png(16), await png(32), await png(48)]);
writeFileSync(resolve(root, 'public/favicon.ico'), ico);

console.log('favicon.ico + apple-touch-icon.png generated');
