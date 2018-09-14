/**
 * @namespace state/auth
 */

 /**
 @TODO all the stuff with the jwt and accessing the user info held within goes here

inspired by https://alligator.io/vuejs/vue-jwt-patterns/

*/


const state = {
	apiToken: null // the api token
};

const mutations =  {
	setAPIToken(state, value) {
		state.apiToken = value;
	},

	invalidateAPIToken(state) {
		state.apiToken = null;
	}
}

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
		if (!state.apiToken) {
			return null
		}
		return JSON.parse(atob(state.apiToken.split('.')[1])).uid
	}
};


export default {
	namespaced: true,
	state,
	mutations,
	getters
}
