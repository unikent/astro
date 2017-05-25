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

								if(error.reason && typeof error.reason === 'object') {

									Object.keys(error.reason).forEach(field => {
										error.reason[field] = error.reason[field].join(' | ');
									});

									const errors = unflatten(error.reason, { safe: true });

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
