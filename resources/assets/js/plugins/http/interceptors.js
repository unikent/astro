import Vue from 'vue';
import { unflatten } from 'flat';

const vue = new Vue();
/* global Promise */

export default (http, store, router) => {
	http.interceptors.response.use(
		response => response,
		error => {
			const { response } = error

			if(Array.isArray(response.data.errors)) {

				switch(response.status) {
					case 422:
						if(response.data.errors) {
							response.data.errors.forEach(error => {

								// notification to the user
								// vue.$notify({
								// 	title: 'Error',
								// 	message: 'There are some problems on your page.' + error,
								// 	type: 'error'
								// });

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

					case 401:
						if(response.data.errors) {
								vue.$confirm(
									`Your authenticated session has expired.
									Please click OK to reload the current page.
									Note: all unsaved data will be lost.`,
									'Sorry!', {
										confirmButtonText: 'OK',
										cancelButtonText: 'Cancel',
										type: 'warning'
									}
								).then(() => {
									// reload the current page
									router.go();
								}).catch(() => {
									// do nothing, they have decided not to reload
								});
						}
						break;
				}
			}

			return Promise.reject(error);
		}
	)
};
