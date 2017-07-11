import Config from 'classes/Config';
import DefinitionClass from 'classes/Definition';

/* global self, console, document */
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

export const findParent = (searchFor, el, exact = false) => {
	const match = exact ? matchExactly : matchByNodeName;

	if(match(el, searchFor)) {
		return el;
	}

	while(el) {
		if(match(el, searchFor)) {
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
};

export const clamp = ({ val, min, max }) => {
	return Math.max(Math.min(val, max), min);
};

// Adapted from https://gist.github.com/desandro/4206095
export const smoothScrollTo = (options) => {
	const { element: el, y, x, duration, easing } = Object.assign(
		{
			element: document.body,
			x: 0,
			y: 0,
			duration: '0.3s',
			easing: 'ease-out'
		},
		options
	);

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

