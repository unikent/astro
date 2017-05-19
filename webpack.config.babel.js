import path from 'path';
import webpack from 'webpack';

import SvgStorePlugin from 'external-svg-sprite-loader/lib/SvgStorePlugin';
import WebpackNotifierPlugin from 'webpack-notifier';
import ExtractTextPlugin from 'extract-text-webpack-plugin';
import FriendlyErrorsPlugin from 'friendly-errors-webpack-plugin';

/* global __dirname, process */

const
	resolve = (dir) => path.resolve(__dirname, `resources/assets/${dir}`),
	isProduction = process.env.NODE_ENV === 'production';

export default {
	entry: [
		resolve('js/app.js'),
		resolve('sass/app.scss')
	],

	output: {
		path: path.resolve(__dirname, 'public'),
		filename: 'js/[name].js',
		publicPath: '/'
	},

	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					loaders: {
						js: 'babel-loader',
						scss: ExtractTextPlugin.extract({
							use: ['css-loader', 'sass-loader'],
							fallback: 'vue-style-loader'
						}),
						css: ExtractTextPlugin.extract({
							use: 'css-loader',
							fallback: 'vue-style-loader'
						})
					}
				}
			},

			{
				test: /\.jsx?$/,
				exclude: /node_modules/,
				loader: 'babel-loader'
			},

			{
				test: /\.css$/,
				loaders: ['style-loader', 'css-loader']
			},

			{
				test: /\.s[ac]ss$/,
				use: ExtractTextPlugin.extract({
					fallback: 'style-loader',
					use: [
						'css-loader',
						{
							loader: 'resolve-url-loader' + (!isProduction ? '?sourceMap' : '')
						},
						{
							loader: 'sass-loader',
							options: {
								precision: 8,
								outputStyle: 'expanded',
								sourceMap: true
							}
						}
					]
				})
			},

			{
				test: /\.html$/,
				loaders: ['html-loader']
			},

			{
				test: /\.(png|jpe?g|gif)$/,
				loader: 'file-loader',
				options: {
					name: 'images/[name].[ext]?[hash]'
				}
			},

			{
				test: /\.svg$/,
				exclude: /node_modules/,
				loader: 'external-svg-sprite-loader'
			},

			{
				test: /\.(woff2?|ttf|eot|svg|otf)$/,
				include: /node_modules/,
				loader: 'file-loader',
				options: {
					name: 'fonts/[name].[ext]?[hash]'
				}
			},

			{
				test: /\.(cur|ani)$/,
				loader: 'file-loader',
				options: {
					name: '[name].[ext]?[hash]'
				}
			}
		]
	},

	plugins: [
		new webpack.ProvidePlugin({
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery',
			'window.axios': 'axios'
		}),

		new SvgStorePlugin(),

		new WebpackNotifierPlugin({
			title: `Astro (${isProduction ? 'prod' : 'dev'})`,
			contentImage: path.resolve(__dirname, 'public/img/logo.png'),
			alwaysNotify: true
		}),

		new webpack.optimize.CommonsChunkPlugin({
			name: 'vendor',
			minChunks: module => {
				return (
					module.context && module.context.indexOf('node_modules') !== -1 &&
					module.resource && !module.resource.match(/\.scss$/)
				);
			}
		}),

		new webpack.optimize.CommonsChunkPlugin({
			name: 'manifest'
		}),

		new ExtractTextPlugin({
			filename: 'css/[name].css'
		}),

		new FriendlyErrorsPlugin(),

		...(
			isProduction ?
			[
				new webpack.DefinePlugin({
					'process.env': {
						NODE_ENV: '"production"'
					}
				}),

				new webpack.optimize.UglifyJsPlugin({
					sourceMap: true,
					compress: {
						warnings: false
					}
				})
			] :
			[]
		)

		// TODO: set up hmr + dev server, stats (to be compatible with Laravel's
		// mix-manifest file), and copy plugin in case we need it later
		// new webpack.HotModuleReplacementPlugin(),
		// webpack-stats-plugin
		// copy-webpack-plugin
	],

	resolve: {
		symlinks: false,
		extensions: ['*', '.js', '.vue', '.json'],
		alias: {
			// necessary for vue
			'vue$'      : 'vue/dist/vue.common.js',
			'classes'   : resolve('js/classes'),
			'components': resolve('js/components'),
			'directives': resolve('js/directives'),
			'mixins'    : resolve('js/mixins'),
			'plugins'   : resolve('js/plugins'),
			'store'     : resolve('js/store'),
			'views'     : resolve('js/components/views'),
			'IconPath'  : resolve('icons')
		}
	},

	devtool: isProduction ? false : 'cheap-eval-source-map',
};
