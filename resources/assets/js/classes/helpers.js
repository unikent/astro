import Config from 'classes/Config';

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

export const isIframe = win.self !== win.top;

export const debug = (output) => {
	if(!Config.get('debug', false)) {
		return;
	}

	console.info(output);
};
