import api from './api';
import interceptors from './interceptors';

export default (Vue, { store, router }) => {
	interceptors(api, store, router);

	Object.defineProperty(Vue.prototype, '$api', {
		get() {
			return api;
		}
	});
};