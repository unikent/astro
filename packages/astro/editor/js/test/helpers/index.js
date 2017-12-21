import { win } from 'classes/helpers';

export const iframeContext = (cb) => {
	// pretend we're in an iframe (window.self !== window.top)
	win.self = {};

	cb();

	// revert back
	win.self = win.top;
};
