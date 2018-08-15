import webpackConfig from '../../../../webpack.config.babel';
import nodeExternals from 'webpack-node-externals';
import { JSDOM } from 'jsdom';

/* global global */

global.DOMParser = new JSDOM().window.DOMParser;

const { module, resolve } = webpackConfig;

export default {
	target: 'node',
	externals: [nodeExternals()],
	module,
	resolve
};
