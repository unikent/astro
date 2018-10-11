/**
 * @namespace state/auth
 */

/**
 inspired by https://alligator.io/vuejs/vue-jwt-patterns/
	NOTE: username is only ever updated when a valid jwt is set.
 We don't invalidate the username just because jwt is invalid as
 most of the time we are just refreshing the jwt and it is the same
 user, and we don't want to lose all of the state in the editor.
 */

const state = {
	apiToken: null, // the api token
	authenticatingPromise: null, // promise that completes when we are authenticated
	authenticatedResolver: null,
	username: null,
};

const mutations = {
	setAPIToken(state, newToken) {
		state.apiToken = newToken;
		// if we have a token AND we had things waiting for authentication
		// then resolve the waiting...
		if (state.apiToken) {
			state.username = JSON.parse(atob(state.apiToken.split('.')[1])).uid;
			if (state.authenticatedResolver) {
				state.authenticatedResolver();
				state.authenticatedResolver = null;
			}
			state.authenticatingPromise = Promise.resolve(state.apiToken);
		}
		// voiding the token - nothing is waiting yet
		else if (!state.authenticatedResolver) {
			state.authenticatingPromise = new Promise((resolve, reject) => {
				state.authenticatedResolver = resolve;
			});
		}
	},

	invalidateAPIToken(state, oldToken) {
		// if we have a valid token different to this one, just return, you can replay your request
		// with the current token.
		if (state.apiToken !== oldToken && state.apiToken !== null) {
			return;
		}
		state.apiToken = null; // this triggers the AuthIFrame
		// if we aren't already reauthenticating, then we need to setup the shared
		// promise and resolver
		if (!state.authenticatedResolver) {
			state.authenticatingPromise = new Promise((resolve, reject) => {
				state.authenticatedResolver = resolve;
			});
		}
	},
};

const getters = {
	jwtData(state) {
		// JWT's are two base64-encoded JSON objects and a trailing signature
		// joined by periods. The middle section is the data payload.
		if (state.apiToken) {
			return JSON.parse(atob(state.apiToken.split('.')[1]))
		}
		return {};
	},

	hasAPIToken(state) {
		return null !== state.apiToken;
	},

	username(state) {
		return state.username;
	},

	getAPIToken(state) {
		return state.authenticatingPromise ? state.authenticatingPromise : Promise.resolve(state.apiToken);
	}
};

const actions = {

	/**
	 * Wait for us to be reauthenticated. Include the token we were using which failed (if any)
	 * @param context
	 * @param failedToken
	 * @returns {null|Promise.<null>|*|Promise}
	 */
	waitForReauthentication(context, failedToken = null) {
		context.commit('invalidateAPIToken', failedToken);
		return context.state.authenticatingPromise;
	},
};

export default {
	namespaced: true,
	state,
	mutations,
	getters,
	actions,
}
