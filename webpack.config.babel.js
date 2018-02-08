import path from 'path';
import webpack from 'webpack';
import SvgStorePlugin from 'external-svg-sprite-loader/lib/SvgStorePlugin';
import WebpackNotifierPlugin from 'webpack-notifier';
import ExtractTextPlugin from 'extract-text-webpack-plugin';
import FriendlyErrorsPlugin from 'friendly-errors-webpack-plugin';
import dotenv from 'dotenv';

// load from .env into process.env
dotenv.config();

/* global __dirname, process */

const
	resolve = (dir) => path.resolve(__dirname, `resources/assets/${dir}`),
	isProduction = process.env.NODE_ENV === 'production';

export default {
	entry: [
		'babel-polyfill',
		resolve('js/app.js'),
		resolve('sass/app.scss')
	],

	output: {
		path: path.resolve(__dirname, 'public/build'),
		filename: 'js/[name].js',
		publicPath: isProduction ? '/site-editor/build/' : '/build/'
	},

	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					loaders: {
						js: {
							loader: 'babel-loader',
							options: {
								presets: [ // copied and pasted to make this still work with absolute theme / blocks directory
									require.resolve('babel-preset-es2015'),
									require.resolve('babel-preset-stage-2')
								]
							}
						},
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
						},
						{
							loader: '@epegzz/sass-vars-loader',
							options: {
								files: [
									resolve('shared/vars.json')
								]
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
			},

		]

	},


	plugins: [
		new webpack.ProvidePlugin({
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
			'vue$' : 'vue/dist/vue.common.js',

			// Astro aliases
			'classes'   : resolve('js/classes'),
			'components': resolve('js/components'),
			'directives': resolve('js/directives'),
			'helpers'   : resolve('js/helpers'),
			'mixins'    : resolve('js/mixins'),
			'plugins'   : resolve('js/plugins'),
			'store'     : resolve('js/store'),
			'views'     : resolve('js/views'),
			'IconPath'  : resolve('icons'),
       // "@theme" replaces "cms-prototype-blocks"
			'@theme'		: process.env.DEFINITIONS_PATH 

			// temporary "package" alias
			'@profiles': resolve('js/profiles')
		}
	},
	resolveLoader: { // required for absolute theme / blocks directory path
		modules: [
			path.resolve(__dirname, 'node_modules'),
			'node_modules'
		],
		extensions: ['.js', '.json'],
		mainFields: ['loader', 'main']
	},

	devtool: isProduction ? false : 'source-map',
};
