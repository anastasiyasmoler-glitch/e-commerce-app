import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react()],
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        rollupOptions: {
            input: 'src/app.jsx',
            output: {
                entryFileNames: 'app.js',
                assetFileNames: 'app.css',
                inlineDynamicImports: true,
            },
        },
    },
});
