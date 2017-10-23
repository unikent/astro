import api from 'plugins/http/api';

/**
 * Simple interface for interacting with user roles and permissions
 * 
 * permissions is an array with the following structure
 * 
[
		{
			"name": "Create Subsites",
			"slug": "subsite.create",
			"roles": [
				"site.owner"
			]
		},
		...
 */
const state = {
	roles: [],
	currentRole: '',
	globalRole: '',
};

const getters = {
	getCurrentRole(state, getters) {
		return state.currentSiteRole;
	},

	getRoles(state, getters) {
		return state.roles;
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
		const matchedRole = state.roles.find(function(value) {
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
		api
			.get(`sites/${payload.site_id}?include=users`)
			.then(({data}) => {
				const userList = data.data.users;
				if (userList) {
					const currentUser = userList.find((element) => element.username === payload.username);
					commit('setCurrentRole', currentUser.role);
				}
				else {
					commit('setCurrentRole', {});
				}
			});
	},

	/**
	 * gets list of the logged in user's gloabl role 
	 * called from TopBar.vue->mounted
	 * 
	 * @param {string} username - the name of the user 
	 */
	loadGlobalRole({commit, state}, username) {	
		// note - username for admin is Admin, make assumption that all username should be lowercase
		api
			.get(`users/${username.toLowerCase()}`)
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
		state.roles = permissions;
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