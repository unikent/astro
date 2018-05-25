import Vue from 'vue';
import { unflatten } from 'flat';
import Config from 'classes/Config';
import { eventBus } from 'plugins/eventbus';

/* global Promise, console */
/* eslint-disable no-console */

export default class Interceptors {

	requestInterceptor;
	responseInterceptor;

	constructor(http, store, router) {
		this.http = http;
		this.store = store;
		this.router = router;
		this.vue = new Vue();
		this.addRequestInterceptor();
		this.addResponseInterceptor();
	}

	addRequestInterceptor() {
		this.requestInterceptor = this.http.interceptors.request.use(
			request => request,
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
		return new Promise((resolve, reject) => {

			// resolve or reject
		});
	}

	handleTooManyRequests(reponse) {
		if(response.headers['x-ratelimit-reset']) {
			console.log(new Date(response.headers['x-ratelimit-reset'] * 1000));
		}

		return Promise.reject(error);
	}
}
