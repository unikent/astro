import Config from 'classes/Config';
import DefinitionClass from 'classes/Definition';

/* global self, console */
/* eslint-disable no-console */

let winObj;

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

export const debug = (output) => {
	if(!Config.get('debug', false)) {
		return;
	}

	console.info(output);
};

export const uuid = (a) => {
	return a ?
		(a^Math.random() * 16 >> a / 4).toString(16) :
		([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, uuid);
};

export const Definition = (
	isIframe ? win.top.astroDefinition : (win.astroDefinition = DefinitionClass)
);

const matchByNodeName = (node, search) => {
	return node.nodeName.toLowerCase() === search;
};

const matchExactly = (node, search) => {
	return node === search;
};

export const findParent = (searchFor, el, exact = false, ignore = false) => {
	const match = exact ? matchExactly : matchByNodeName;

	if(match(el, searchFor) && !ignore) {
		return el;
	}

	while(el) {
		if(match(el, searchFor) && !ignore) {
			return el;
		}
		el = el.parentNode;
	}

	return null;
};

export const getTopOffset = (el) => {
	let pos = 0;

	while(el) {
		pos += (el.offsetTop - el.scrollTop + el.clientTop);
		el = el.offsetParent;
	}

	return pos;
}
