const
	{ mix } = require('laravel-mix'),
	webpack = require('webpack'),
	path = require('path'),
	SvgStorePlugin = require('external-svg-sprite-loader/lib/SvgStorePlugin');

mix
	.webpackConfig({
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
				CMS_ALIAS: path.resolve(__dirname, 'resources/assets/js'),
				IconPath: path.resolve(__dirname, 'resources/assets/icons')
			}
		}
	})
	// .copy(
	// 	'node_modules/kent-bar/build/deploy/assets/app.js',
	// 	'public/js/kent-bar.js'
	// )
	// .copy(
	// 	'node_modules/kent-bar/build/deploy/assets/main.css',
	// 	'public/css/kent-bar.css'
	// )
	.js('resources/assets/js/app.js', 'public/js')
	.sass('resources/assets/sass/app.scss', 'public/css')
	.extract(['vue', 'jquery', 'axios', 'tether', 'bootstrap', 'element-ui'])
	.sourceMaps();
