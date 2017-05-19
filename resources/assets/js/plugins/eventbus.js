import Vue from 'vue';
import { isIframe } from 'classes/helpers';

/* global window */

export const eventBus = (
	isIframe ?
		window.top.eventBus :
		new Vue()
);

if(!isIframe) {
	window.eventBus = eventBus;
}

export default (Vue) => {
	Object.defineProperties(Vue.prototype, {
		$bus: {
			get() {
				return eventBus;
			}
		}
	});
};
