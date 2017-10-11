/**
 * Simple interface for interacting with user roles and permissions
 * 
 * permissions is an object with the following structure
 * 
 * permissions {
 * 	'<permission name>' : [<'role name', 'role name' ...]
 * 
 * }
 */
const state = {
	permissions: {
		'editSite': ['admin', 'moderator', 'spanner']
	},
	role: 'moderator',
};

const getters = {
	/**
	 * checks if the current user has a specific permission
	 * the role of the current user has with the current site is assumed to be in the state as role
	 * 
	 * @param {object} state - the current vuex state - automagically added
	 * @param {string} permission_name - the name of a permission e.g. 'editSite', 'addSite' etc
	 * @returns {boolean} - true if the role has the permission_name
	 */
	userCan: (state, getters) => (permission_name) => {
		if (state.permissions.hasOwnProperty(permission_name)) {
			return state.permissions[permission_name].includes(state.role);
		} 
		else {
			return false;
		}
	}
};

const actions = {};

const mutations = {

	/**
	 * updates the role that the current user has in the current site
	 * @param {*} state 
	 * @param {string} role_name - the name of the role being assigned
	 */
	setRole(state, role_name) {
		state.role = role_name;
	},

	/**
	 * Used to initially set the permissions the editor understands
	 * 
	 * @param {*} state 
	 * @param {object} permissions - the set of permissions as received from the API
	 */
	setPermissions(state, permissions) {

	}
	
};

export default {
	state,
	getters,
	mutations
};