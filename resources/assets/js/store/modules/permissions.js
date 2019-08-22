import api from 'plugins/http/api';
import { debug } from 'classes/helpers';

/**
 * Simple interface for interacting with user roles and permissions
 * @namespace state/permissions
 *
 * @property {Array}	permissions		Array of objects of permissions, with the following format...
	 * @property {object}
	 * @property {string}	name 	the name of the permission, ie. 'Create Subsites'
	 * @property {Array}	roles	list of role slugs which have this permission ie. ['site.ower','site.editor' etc etc]
	 * @property {string}	slug	slug of the name of the permssion, ie. 'subsite.create'

 * @property {string}	currentRole		slug of the users' role within the site ie. 'site.owner' - set to empty string if they have no role
 * @property {string}	globalRole		slug of the users' global role within the syste ie. 'admin' or 'user' - set to empty string if they have no role
 * @property {number} currentSiteId		id of the site that the user's role is loaded on
 */

const state = {
	permissions: [],
	currentRole: '',
	globalRole: '',
	currentSiteId: null,
};

const getters = {
	getCurrentRole(state) {
		return state.currentRole;
	},

	getPermissions(state) {
		return state.permissions;
	},

	getGlobalRole(state) {
		return state.globalRole;
	},

	/**
	 * Have we loaded the role for the user on this site?
	 * @param state
	 * @returns {boolean}
	 */
	userSitePermissionsReady(state) {
		return (state.globalRole === 'admin' || state.currentSiteId !== null) && (state.permissions && state.globalRole) ? 1 : 0;
	},

	/**
	 * can the user perform the requested action
	 * if the user is a global admin then let them do anything
	 *
	 * @param {object} state - the vuex state
	 * @param {string} the permission to check i.e. subsite.edit, subsite.create
	 * @returns {bool} true or false
	 */
	canUser: (state) => (permissionSlug) => {

		let permitted = false;

		// if the user has the global role of admin then then they can do anything
		if (state.globalRole === 'admin') {
			return true;
		}
		const matchedRole = state.permissions.find(function(value) {
			if (value.slug == this.permissionSlug) {
				return true;
			}
		}, { permissionSlug });

		if (matchedRole) {
			permitted = matchedRole.roles.includes(state.currentRole);
		}

		return permitted;
	}
};

const actions = {

	/**
	 * gets list of roles from the api can calls mutation to store this in the state
	 * called from TopBar.vue->mounted
	 *
	 * @param {object} state - the vuex state
	 */
	loadPermissions({ commit }) {
		// TODO: catch errors
		api
			.get('permissions?include=roles')
			.then((response) => {
				let permissions = response.data.data;
				commit('setPermissions', permissions);
			})
	},

	/**
	 * gets list of a user's roles from the api can calls mutation to store this in the state
	 * called from Editor.vue->created
	 *
	 * @param {object} state - the vuex state
	 * @param {object}	payload
	 * @param {int}		payload.siteId  - the id of the current site
	 * @param {string}	payload.username -  the name of the user
	 */
	loadSiteRole({ commit, state }, payload) {
		commit('setCurrentRole', '');
		commit('setCurrentSiteId', null);
		if(payload.siteId) {
			return api
				.get(`sites/${payload.siteId}?include=users`)
				.then(({data}) => {
					const userList = data.data.users;
					if (userList) {
						const currentUser = userList.find((element) => element.username === payload.username);
						commit('setCurrentSiteId', payload.siteId);
						if (currentUser) {
							commit('setCurrentRole', currentUser.role);
						}
						else {
							commit('setCurrentRole', '');
						}
					}
					else {
						commit('setCurrentRole', '');
					}
				})
				.catch(error => {
					debug(`[Error loading site roles] ${error}`);
				});
		}

	},

	/**
	 * gets list of the logged in user's global role
	 * called from TopBar.vue->mounted
	 *
	 * @param {string} username - the name of the user
	 */
	loadGlobalRole({ commit, state }, username) {
		api
			.get(`users/${username}`)
			.then(({ data }) => {
				commit('setGlobalRole', data.data.global_role);
			})
			.catch(() => {
				commit('setGlobalRole', '');
			})
	}

};

const mutations = {

	/**
	 * Used to initially set the permissions the editor understands
	 *
	 * @param {object} vuex state
	 * @param {object} permissions - the set of permissions as received from the API
	 */
	setPermissions(state, permissions) {
		state.permissions = permissions;
	},

	/**
	 * sets the current role for the site
	 *
	 * @param {object} state - the vuex state
	 * @param {string} currentRole
	 */
	setCurrentRole(state, currentRole) {
		state.currentRole = currentRole;
	},

	/**
	 * sets the user's global role
	 *
	 * @param {object} state - the vuex state
	 * @param {string} globalRole
	 */
	setGlobalRole(state, globalRole) {
		state.globalRole = globalRole;
	},

	/**
	 * Sets the id of the site the current user role is for
	 * @param {object} state - the vuex state
	 * @param {number|null} siteId - The id of the site the user's role is currently loaded for.
	 */
	setCurrentSiteId(state, siteId) {
		state.currentSiteId = siteId;
	}
};

export default {
	state,
	getters,
	mutations,
	actions
};
