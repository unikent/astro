import api from './api';
import Interceptors from './Interceptors';

export default (Vue, { store, router }) => {

	const interceptors = new Interceptors(api, store, router);

	Object.defineProperty(Vue.prototype, '$api', {
		get() {
			return api;
		}
	});
};
