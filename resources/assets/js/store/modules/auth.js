/**
 * @namespace state/auth
 */

 /**
 @TODO all the stuff with the jwt and accessing the user info held within goes here

inspired by https://alligator.io/vuejs/vue-jwt-patterns/


hold the jwt

functionality:

	- view user data in the jwt
	- set the jwt
	- reset the jwt
*/


// import Vue from 'vue';

// const vue = new Vue();


const state = {
	apiToken: null // the api token
};

const mutations =  {
	setAPIToken(state, value) {
		state.apiToken = value;
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
	}
};


export default {
	namespaced: true,
	state,
	mutations,
	getters
}
