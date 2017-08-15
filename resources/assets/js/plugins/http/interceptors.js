import Vue from 'vue';
import { unflatten } from 'flat';

const vue = new Vue();

/* global Promise */

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

			if(Array.isArray(response.data.errors)) {

				switch(response.status) {
					case 422:
						if(response.data.errors) {
							response.data.errors.forEach(error => {

								vue.$snackbar.open({
									message: `
										${error.message}.\nCheck validation errors for more details.
									`
								});

								if(error.details && typeof error.details === 'object') {

									Object.keys(error.details).forEach(field => {
										error.details[field] = error.details[field].join(' | ');
									});

									const errors = unflatten(error.details, { safe: true });

									store.commit('updateErrors', errors);
								}

							});
						}
						break;
				}
			}

			return Promise.reject(error);
		}
	)
};
