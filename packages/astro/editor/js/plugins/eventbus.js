import Vue from 'vue';
import { isIframe, win } from 'classes/helpers';

export const eventBus = (
	isIframe ?
		win.top.eventBus :
		new Vue()
);

if(!isIframe) {
	win.eventBus = eventBus;
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
