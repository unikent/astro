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
 
 * @property {string}	currentRole		slug of the users' role within the site ie. 'site.ower' - set to empty string if they have no role
 * @property {string}	globalRole		slug of the users' gloabl role within the syste ie. 'admin' or 'user' - set to empty string if they have no role
 */

const state = {
	permissions: [],
	currentRole: '',
	globalRole: '',
};

const getters = {
	getCurrentRole(state, getters) {
		return state.currentSiteRole;
	},

	getPermissions(state, getters) {
		return state.permissions;
	},

	getGlobalRole(state, getters) {
		return state.globalRole;
	},

	/**
	 * can the user perform the requested action
	 * if the user is a global admin then let them do anything
	 * 
	 * @param {object} state - the vuex state
	 * @param {string} the permission to check i.e. subsite.edit, subsite.create
	 * @returns {bool} true or false
	 */
	canUser: (state, getters) => (permissionSlug) => {
		
		let permitted = false;
		
		// if the user has the global role of admin then then they can do anything
		if (state.globalRole === 'admin') {
			return true;
		}
		const matchedRole = state.permissions.find(function(value) {
			if (value.slug == this.permissionSlug) {
				return true;
			}
		}, {permissionSlug});
			
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
	loadPermissions({commit, state}) {		
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
	 * @param {int}		payload.site_id  - the id of the current site
	 * @param {string}	payload.username -  the name of the user
	 */
	loadSiteRole({commit, state}, payload) {
		return api
			.get(`sites/${payload.site_id}?include=users`)
			.then(({data}) => {
				const userList = data.data.users;
				if (userList) {
					const currentUser = userList.find((element) => element.username === payload.username);
					if (currentUser) {
						commit('setCurrentRole', currentUser.role);
					} else {
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
	},

	/**
	 * gets list of the logged in user's gloabl role 
	 * called from TopBar.vue->mounted
	 * 
	 * @param {string} username - the name of the user 
	 */
	loadGlobalRole({commit, state}, username) {	
		api
			.get(`users/${username}`)
			.then(({data}) => {
				commit('setGlobalRole', data.data.global_role);
			})
			.catch(()=> {
				commit('setGlobalRole', '');
			})
	}

}

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
	 * @param {string} gloablRole 
	 */
	setGlobalRole(state, gloablRole) {
		state.globalRole = gloablRole;
	}
};

export default {
	state,
	getters,
	mutations,
	actions
};