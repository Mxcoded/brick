import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { copyFileSync, mkdirSync, readdirSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        {
            name: 'copy-fonts',
            closeBundle() {
                const src = join(__dirname, 'public', 'build');
                const targets = [
                    {
                        src: 'node_modules/@fortawesome/fontawesome-free/webfonts',
                        dest: join(src, 'webfonts'),
                    },
                    {
                        src: 'node_modules/bootstrap-icons/font/fonts',
                        dest: join(src, 'assets', 'fonts'),
                    },
                ];

                for (const { src: srcDir, dest: destDir } of targets) {
                    const fullSrc = join(__dirname, srcDir);
                    if (!existsSync(destDir)) {
                        mkdirSync(destDir, { recursive: true });
                    }
                    for (const file of readdirSync(fullSrc)) {
                        copyFileSync(join(fullSrc, file), join(destDir, file));
                    }
                }
            },
        },
    ],
});
