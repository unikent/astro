import Vue from 'vue';
import { unflatten } from 'flat';
import Config from 'classes/Config';
import { win, isIframe } from 'classes/helpers';

/* global Promise, console */
/* eslint-disable no-console */

// we want to share some bits between the top window and iframe
let shared = isIframe ?
	win.top.astroInterceptorShared :
	(win.astroInterceptorShared = { unauthorizedPromise: null, vue: new Vue() });

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
		let token = this.store.getters['auth/getAPIToken'];
		request.headers['Authorization'] = `Bearer ${token}`;
		return request;
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

		if(!shared.unauthorizedPromise) {
			shared.unauthorizedPromise = new Promise((resolve, reject) => {
				shared.vue.$confirm(
					`Your authenticated session has expired.
					Please click OK to reload the current page.
					Note: all unsaved data will be lost.`,
					'Session Expired', {
						confirmButtonText: 'OK',
						cancelButtonText: 'Cancel',
						type: 'warning'
					}
				).then(() => {
					// reload the current page
					shared.router.push('/site/1');
				}).catch(() => {
					reject();
					shared.unauthorizedPromise = null;
				});
			});
		}

		return shared.unauthorizedPromise;
	}

	handleTooManyRequests(reponse) {
		if(response.headers['x-ratelimit-reset']) {
			console.log(new Date(response.headers['x-ratelimit-reset'] * 1000));
		}

		return Promise.reject(error);
	}
}
