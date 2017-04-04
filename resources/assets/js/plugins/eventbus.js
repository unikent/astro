import Vue from 'vue';

const bus = new Vue();

export default (Vue) => {
	Object.defineProperties(Vue.prototype, {
		$bus: {
			get() {
				return bus;
			}
		}
	});
};