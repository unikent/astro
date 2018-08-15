import webpackConfig from '../../../../webpack.config.babel';
import { JSDOM } from 'jsdom';

/* global global */

global.DOMParser = new JSDOM().window.DOMParser;

const { module, resolve } = webpackConfig;

export default {
	target: 'node',
	module,
	resolve
};
