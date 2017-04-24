import Vue from 'vue';

const vue = new Vue();

/* global window, Promise */

export default (http, store, router) => {
	// http.interceptors.request.use(
	// 	config => {
	// 		console.log(config);
	// 		return config;
	// 	},
	// 	error => {
	// 		return Promise.reject(error);
	// 	}
	// );
	http.interceptors.response.use(
		response => response,
		error => {
			const { response } = error

			// if([401, 400].indexOf(response.status) > -1) {
			// 	window.location = '/auth/login';
			// }

			// TODO: hook up to snackbar
			if(Array.isArray(response.data.errors)) {
				// store.dispatch('setMessage', {
				// 	type: 'error',
				// 	message: response.data.errors
				// })

				switch(response.status) {
					case 422:
						response.data.errors.forEach(error => {
							vue.$snackbar.open({
								message: `
									${error.type}.\nCheck validation errors for more details.
								`
							});
						});
						break;
				}
			}

			return Promise.reject(error)
		}
	)
};
