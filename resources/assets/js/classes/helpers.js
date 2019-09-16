import Config from 'classes/Config';
import DefinitionClass from 'classes/Definition';
import Vue from 'vue';

/* global self, console, document */
/* eslint-disable no-console */

let winObj;

const vue = new Vue();

// window object exists
if(typeof self === 'object' && self.self === self) {
	winObj = self;
}
// fake our window object for testing/SSR
else {
	winObj = {};
	winObj.self = winObj;
	winObj.top = winObj;
}

export const win = winObj;

export const inIframeContext = () => {
	return win.self !== win.top;
};

export const isIframe = inIframeContext();

export const debug = (output, type = 'info') => {
	if(!Config.get('debug', false)) {
		return;
	}

	console[type](output);
};

export const uuid = (a) => {
	return a ?
		(a^Math.random() * 16 >> a / 4).toString(16) :
		([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, uuid);
};

export const Definition = (
	isIframe ? win.top.astroDefinition : (win.astroDefinition = DefinitionClass)
);

export const clamp = ({ val, min, max }) => {
	return Math.max(Math.min(val, max), min);
};

// Adapted from https://gist.github.com/desandro/4206095
export const smoothScrollTo = (options) => {
	const { element: el, y, x, duration, easing } = {
		element: document.body,
		x: 0,
		y: 0,
		duration: '0.3s',
		easing: 'ease-out',
		...options
	};

	// clamp values to min & max scroll
	// then remove the current scroll position
	let
		targetX = clamp({
			val: x,
			min: 0,
			max: el.scrollWidth - win.innerWidth
		}) - win.scrollX,

		targetY = clamp({
			val: y,
			min: 0,
			max: el.scrollHeight - win.innerHeight
		}) - win.scrollY;

	el.style.transition = `transform ${duration} ${easing}`;
	el.style.transform = `translate(${-targetX}px, ${-targetY}px)`;

	const onEnd = (e) => {
		if(e.target !== el) {
			return;
		}

		el.style.transition = null;
		el.style.transform = null;

		win.scrollTo(x, y);

		el.removeEventListener('transitionend', onEnd, false);
	};

	el.addEventListener('transitionend', onEnd, false);
};

export const readingSpeedFromString = (str = '', timeToNotice = 500) => {
	// average reading speed (CPM) + half a second to notice snackbar
	return Math.ceil((str.length * 60000) / 863) + timeToNotice;
};

/**
 * Get a rough estimate of time passed between a date and now.
 * @param {String} date - A date parsable by new Date(date)... although this should not include the timezone at the end or things will get "fun"
 * @param {boolean} about - True to include the word "about" before the result if the time is not exact
 * @param {integer|null} timezoneOffset - The timezone offset from UCT for the passed date string
 * @returns {string} - A nicely formatted string, e.g "about 5 minutes ago"
 */
export const prettyDate = (date, about = true, timezoneOffset = null) => {
    if(!Number.isInteger(timezoneOffset)) {
        timezoneOffset = new Date().getTimezoneOffset();
    }
    // get "now" in UCT seconds
    const now = Date.now() + (new Date().getTimezoneOffset() * 60000);
    const then = new Date(date).getTime() + (timezoneOffset * 60000);
    let diff = (now - then) / 1000,
		unit = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year'],
		num = [60, 60, 24, 7, 4.35, 12],
		i = 0;

	while(i < num.length && diff >= num[i]) {
		diff /= num[i++];
	}

	diff = Math.round(diff);

	return `${(i > 0 && about) ? 'about ' : ''}${diff} ${unit[i]}${diff === 1 ? '' : 's'} ago`;
};

export const notify = ({ title, message, type }) => {
	// TODO:is notify is overkill for some scenarios?
	// Element UI's $message seems more suited to success messages
	vue.$notify({
		title,
		message,
		type,
		duration: readingSpeedFromString(message, 3000),
		onClick() {
			this.close();
		}
	});
};

// Does the given string start with a vowel? If so return "an", otherwise "a".
// Doesn't cover all cases, but is good enough for now.
export const aOrAn = (str) => str.match(/^[aeiou]/i) ? 'an' : 'a';

export const pageHasBeenPublished = (page) => {
	return page.status !== 'new';
};


/**
 * Get the URL at which the current page can be previewed in the editor.
 *
 * @param {string} domain - The domain name for the site.
 * @param {string} path - The full path of the page to generate the URL for.
 *
 * @returns {string} URL
 */
export const getDraftPreviewURL = (domain, path) => {
	let pattern = Config.get('draft_url_pattern') || '{domain}{path}';
	return pattern.replace(/{domain}/ig, domain).replace(/{path}/ig, path);
};

/**
 * Get the URL at which the published version of the current page can be previewed in the editor.
 *
 * @param {string} domain - The domain name for the site.
 * @param {string} path - The full path of the page to generate the URL for.
 *
 * @returns {string} URL
 */
export const getPublishedPreviewURL = (domain, path) => {
	let pattern = Config.get('published_url_pattern') || '{domain}{path}';
	return pattern.replace(/{domain}/ig, domain).replace(/{path}/ig, path);
};

/**
 * Get a url for an image or a default.
 * @param {String|Object|null} url - URL to the image OR object with url attribute OR null to use default.
 * @param {string|null} defaultUrl - Alternative placeholder image to use (relative to Config.get('assets_base_url') )
 * @returns {string} - url, or Config.get('assets_base_url') + defaultUrl or Config.get('assets_base_url') + Config.get('default_placeholder_image')
 */
export const imageUrl = (src_or_url, defaultUrl) => {
	let result = src_or_url && src_or_url.url ? src_or_url.url: src_or_url;
	if(!result) {
		result = Config.get('assets_base_url');
		if(defaultUrl) {
			result += defaultUrl;
		}
		else {
			result += Config.get('placeholder_image_url');
		}
	}
	return result;
//	return url !== void 0 ? url :
//		Config.get('assets_base_url') + (defaultUrl !== void 0 ? defaultUrl : Config.get('default_placeholder_url'));
};

/**
	* Generates full url to an asset
	* @param {String} path - the relative path to the asset.
	* @returns {String} - the full URL to the asset, taking into account the configured assets base url.
	*/
export const assetsUrl = (path) => {
	return Config.get("assets_base_url") + path;
};

/**
	* Generates full url to the block images
	* @param {String} path - the relative path to the block images.
	* @returns {String} - the full URL to the block images, taking into account the configured block images base url.
	*/
export const blockImageUrl = (path) => {
	return Config.get("block_image_url") + path;
};
