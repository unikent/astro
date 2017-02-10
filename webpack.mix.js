const
	{ mix } = require('laravel-mix'),
	webpack = require('webpack');

mix
	.webpackConfig({
		plugins: [
			new webpack.ProvidePlugin({
				$: 'jquery',
				jQuery: 'jquery',
				'window.jQuery': 'jquery',
				'Tether': 'tether',
				'window.axios': 'axios'
			})
		],
		resolve: {
			symlinks: false
		}
	})
	.copy(
		'node_modules/kent-bar/build/deploy/assets/app.js',
		'public/js/kent-bar.js'
	)
	.copy(
		'node_modules/kent-bar/build/deploy/assets/main.css',
		'public/css/kent-bar.css'
	)
	.js('resources/assets/js/app.js', 'public/js')
	.sass('resources/assets/sass/app.scss', 'public/css')
	.extract(['vue', 'jquery', 'axios', 'tether', 'bootstrap', 'element-ui'])
	.sourceMaps();
