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

								vue.$alert('We saved your page, but we also highlighted a few problems on the page. You\'ll need to check these before you can publish the page.', 'Almost there...', {
									confirmButtonText: 'OK',
									type: 'warning',
									closeOnClickModal: true,
									closeOnPressEscape: true
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
