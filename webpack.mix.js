/* global require, __dirname, process */

const
	{ mix } = require('laravel-mix'),
	webpack = require('webpack'),
	path = require('path'),
	SvgStorePlugin = require('external-svg-sprite-loader/lib/SvgStorePlugin'),
	resolve = (dir) => path.resolve(__dirname, `resources/assets/js/${dir}`);

mix.webpackConfig({
	module: {
		rules: [
			{
				test: /\.svg$/,
				enforce: 'pre',
				exclude: /node_modules/,
				loader: 'external-svg-sprite-loader'
			}
		]
	},

	plugins: [
		new webpack.ProvidePlugin({
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery',
			'Tether': 'tether',
			'window.axios': 'axios'
		}),
		new SvgStorePlugin()
	],

	resolve: {
		symlinks: false,
		alias: {
			classes:    resolve('classes'),
			components: resolve('components'),
			directives: resolve('directives'),
			mixins:     resolve('mixins'),
			plugins:    resolve('plugins'),
			store:      resolve('store'),
			views:      resolve('components/views'),
			IconPath:   path.resolve(__dirname, 'resources/assets/icons')
		}
	}
});


/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.js('resources/assets/js/app.js', 'public/js')
	.sass('resources/assets/sass/app.scss', 'public/css')
	.extract([
		'axios',
		'chart.js',
		'element-ui',
		'jquery',
		'lodash',
		'velocity-animate',
		'vue',
		'vue-chartjs',
		'vue-router',
		'vuedraggable',
		'vuex'
	])
	.sourceMaps();

	// .copy(
	// 	'node_modules/kent-bar/build/deploy/assets/app.js',
	// 	'public/js/kent-bar.js'
	// )
	// .copy(
	// 	'node_modules/kent-bar/build/deploy/assets/main.css',
	// 	'public/css/kent-bar.css'
	// )

mix.browserSync(process.env.APP_HOST || 'localhost');
