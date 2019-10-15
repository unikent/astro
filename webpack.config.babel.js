import path from 'path';
import webpack from 'webpack';
import SvgStorePlugin from 'external-svg-sprite-loader/lib/SvgStorePlugin';
import WebpackNotifierPlugin from 'webpack-notifier';
import ExtractTextPlugin from 'extract-text-webpack-plugin';
import FriendlyErrorsPlugin from 'friendly-errors-webpack-plugin';
import MixManifestPlugin from './resources/assets/js/console/webpack/MixManifestPlugin';
import dotenv from 'dotenv';
import CopyWebpackPlugin from 'copy-webpack-plugin';

// load from .env into process.env
dotenv.config();

/* global __dirname, process, require */

const
	resolve = (dir) => path.resolve(__dirname, `resources/assets/${dir}`),
	isProduction = process.env.NODE_ENV === 'production',
	hmrEnabled = process.argv.includes('--hot'),
	hmrURL = process.env.APP_HMR_URL || 'http://localhost:8080',
	babelLoader = {
		loader: 'babel-loader',
		options: {
			presets: [
				['babel-preset-env'].map(require.resolve)
			],
			plugins: [
				'babel-plugin-transform-class-properties',
				'babel-plugin-transform-object-rest-spread',
				'babel-plugin-transform-object-assign',
				'babel-plugin-array-includes'
			].map(require.resolve)
		}
	};

export default {
	entry: [
		'babel-polyfill',
		resolve('js/app.js'),
		resolve('sass/app.scss')
	],

	output: {
		path: path.resolve(__dirname, 'public/build'),
		filename: isProduction ? 'js/[name].js?[chunkhash]' : 'js/[name].js',
		publicPath:  process.env.PUBLIC_PATH + (hmrEnabled ? `${hmrURL}/build/` : '/build/')
	},

	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					loaders: {
						js: babelLoader,
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
				loader: babelLoader
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
		new SvgStorePlugin(),

		new WebpackNotifierPlugin({
			title: `Astro (${isProduction ? 'prod' : 'dev'})`,
			contentImage: path.resolve(__dirname, 'public/img/logo.png')
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

		new webpack.DefinePlugin({
			__HMR__: JSON.stringify(hmrEnabled),
			__HMR_URL__: JSON.stringify(hmrURL)
		}),

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
				hmrEnabled ? [new webpack.NamedModulesPlugin()] : []
		),

		new MixManifestPlugin({
			filename: 'mix-manifest.json',
			path: path.resolve(__dirname, 'public'),
			url: hmrURL
		}),

		new CopyWebpackPlugin([
			{
				from: 'node_modules/tinymce/skins',
				to: 'css/tinymce/skins'
			},
			{
				from: process.env.DEFINITIONS_PATH + '/blocks/*/*/image.png',
				to: 'img',
				transformPath(targetPath, absolutePath) {
					targetPath = targetPath.replace(/^.*\/([a-z0-9_-]+)\/(v[0-9]+)\/image\.png$/i, 'img/definitions/blocks/$1-$2.png');
					console.log(targetPath);
					return targetPath;
				}
			}
		], { logLevel: 'debug' }
		),
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

			// "@theme" points to the folder of the theme we're using
			'@theme'    : process.env.DEFINITIONS_PATH
		},
		modules: [
			path.resolve(__dirname, 'node_modules')
		]
	},

	resolveLoader: { // required for absolute theme / blocks directory path
		extensions: ['.js', '.json'],
		modules: [
			path.resolve(__dirname, 'node_modules')
		],
		mainFields: ['loader', 'main']
	},

	devServer: MixManifestPlugin.devServerConfig(),

	devtool: isProduction ? false : 'cheap-module-eval-source-map',
};
