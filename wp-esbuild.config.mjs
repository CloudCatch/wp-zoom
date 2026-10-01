import { defineConfig } from '@cloudcatch/wp-esbuild/config';

export default defineConfig( {
	srcDir: 'resources',
	outDir: 'assets',
	minify: process.env.NODE_ENV === 'production',
	sourcemap: process.env.NODE_ENV !== 'production',
	blocksManifest: {
		enabled: false,
	},
	blocks: {
		enabled: false,
	},
	modules: {
		enabled: false,
	},
	js: {
		src: 'resources/js',
		out: 'assets/js',
		glob: '{admin,frontend,calendar}.js',
	},
	scss: {
		src: 'resources/scss',
		out: 'assets/css',
		glob: '{admin,frontend}.scss',
	},
	copy: [
		{
			from: 'resources/fonts',
			to: 'assets/fonts',
		},
		{
			from: 'resources/css/fonts.css',
			to: 'assets/css/fonts.css',
		},
	],
} );
