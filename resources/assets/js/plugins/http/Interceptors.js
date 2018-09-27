import Vue from 'vue';
import { win, isIframe } from 'classes/helpers';

/* global Promise, console */
/* eslint-disable no-console */

// we want to share some bits between the top window and iframe
let shared = isIframe ?
	win.top.astroInterceptorShared :
	(win.astroInterceptorShared = { unauthorizedPromise: null, vue: new Vue() , forbiddenPromise: null});

export default class Interceptors {

	requestInterceptor;
	responseInterceptor;

	constructor(http, store, router) {
		this.http = http;
		this.store = store;
		this.vue = new Vue();
		this.addRequestInterceptor();
		this.addResponseInterceptor();

		if(!isIframe) {
			shared.router = router;
		}
	}

	setAuthToken(request) {
		return this.store.getters['auth/getAPIToken'].then( (token) => {
			request.headers['Authorization'] = `Bearer ${token}`;
			return request;
		});
	}

	addRequestInterceptor() {
		this.requestInterceptor = this.http.interceptors.request.use(
			request => {
				return this.setAuthToken(request)
			},
			error => Promise.reject(error)
		);
	}



	addResponseInterceptor() {
		this.responseInterceptor = this.http.interceptors.response.use(
			response => response,
			error => this.handleResponseError(error)
		);
	}

	removeRequestInterceptor() {
		this.http.interceptors.request.eject(this.requestInterceptor);
	}

	removeReponseInterceptor() {
		this.http.interceptors.response.eject(this.responseInterceptor);
	}

	/**
	 * Take a response error object and when a handler method exists for a
	 * specific status, run it.
	 *
	 * @param      {object}  error   The raw response error.
	 *
	 * @return     {Promise}  A promise that is either rejected or resolved.
	 */
	handleResponseError(error) {
		const status = error.response.statusText.replace(/ /g, '');

		// reject the promise if a handler doesn't exist
		if(typeof this[`handle${status}`] !== 'function') {
			return Promise.reject(error);
		}

		return this[`handle${status}`](error.response);
	}

	handleUnauthorized(response) {
		let failedToken = response.config.headers.Authorization.substr(7);
		if(failedToken === 'null') {
			failedToken = null;
		}
		return this.store.dispatch('auth/waitForReauthentication', failedToken)
			.then((token) => {
				return this.http.request(response.config);
			}).catch((err) => {
			console.log('Im trying...');
			});
	}

	/**
	 * Handles 403 responses from the API.
	 * @param response
	 * @returns {Promise|null}
	 */
	handleForbidden(response) {

		if(!shared.forbiddenPromise) {
			shared.forbiddenPromise = new Promise((resolve, reject) => {
				shared.vue.$confirm(
					`You do not have permission to do something you are doing.
					Please click OK to reload the current page.
					Note: all unsaved data will be lost.
					Alternatively click Cancel and navigate to another page or logout`,
					'Action Forbidden', {
						confirmButtonText: 'OK',
						cancelButtonText: 'Cancel',
						type: 'warning'
					}
				).then(() => {
					// reload the current page
					shared.router.go();
				}).catch(() => {
					reject();
					shared.forbiddenPromise = null;
				});
			});
		}

		return shared.forbiddenPromise;
	}

	handleTooManyRequests(reponse) {
		if(response.headers['x-ratelimit-reset']) {
			console.log(new Date(response.headers['x-ratelimit-reset'] * 1000));
		}
		return Promise.reject(error);
	}
}
