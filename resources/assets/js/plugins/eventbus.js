import Vue from 'vue';

/* global window */

export const eventBus = (
	window.self === window.top ?
		new Vue() :
		window.top.eventBus
);

window.eventBus = eventBus;

export default (Vue) => {
	Object.defineProperties(Vue.prototype, {
		$bus: {
			get() {
				return eventBus;
			}
		}
	});
};
