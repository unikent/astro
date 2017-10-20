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
	currentRole: {}
};

const getters = {
	getCurrentRole(state, getters) {
		return state.currentRole;
	},

	getRoles(state, getters) {
		return state.roles;
	}
};

const actions = {

	/**
	 * gets list of roles from the api can calls mutation to store this in the state
	 * 
	 * called from TopBar.vue->mounted
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
	 * 
	 * called from Editor.vue->created
	 */
	loadSitePermissions({commit, state}, site_id) {
		api
			.get(`sites/${site_id}?include=users`)
			.then(({data}) => {
				const userList = data.data.users;
				if (userList) {
					const currentRole = userList.find((element) => element.name === window.astro.username);
					commit('setCurrentRole', currentRole);
				}
				else {
					commit('setCurrentRole', {});
				}
			});
	}

};

const mutations = {

	/**
	 * Used to initially set the permissions the editor understands
	 * 
	 * @param {*} state 
	 * @param {object} permissions - the set of permissions as received from the API
	 */
	setPermissions(state, permissions) {
		state.roles = permissions;
	},

	/**
	 * sets the current role for the site
	 * @param {*} state 
	 * @param {*} currentRole 
	 */
	setCurrentRole(state, currentRole) {
		state.currentRole = currentRole;
	}
};

export default {
	state,
	getters,
	mutations,
	actions
};