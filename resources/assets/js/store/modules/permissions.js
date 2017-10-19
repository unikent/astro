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
	}
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
	},

	/**
	 * returns the role that the logged in user has in the current site
	 * if we do not have a current site then it returns null
	 */
	role: (state, getters) => () => {

	}
};

const actions = {

	/**
	 * gets list of roles from the api can calls mutation to store this in the state
	 */
	loadGlobalRolls() {

		let permissions = {};
		
		api
			.get(`permissions`, {
				// blocks: page.blocks
			})
			.then(({data}) => {
				permissions = 
				dispatch('fetchSite');
			})



	},

	/**
	 * gets list of a user's roles from the api can calls mutation to store this in the state
	 */
	loadUserRoles() {

	}

};

const mutations = {

	/** REPLACE WITH PAYLOAD
	 * sets the role for a user within a given site
	 * @param	{string}		username eg. 'cfc7' 
	 * @param	{array}			array of users for a given site as returned by /api/v1/sites/1?include=users
	 * @return	{string|null}	the rolename e.g. 'Admin', 'Editor' etc or null is the user has no role
	 */
	setRole(state, user_name, site_roles) {

		role = null;

		if (!Array.isArray(site_roles)) {
			roll = null
		} else {
			currentRole = site_roles.find(user => user.user.username === user_name);
			if (currentRole) {
				roll =  currentRole.role;
			} else {
				roll = null;
			}
		}

		state.role = roll;
	},

	/**
	 * Used to initially set the permissions the editor understands
	 * 
	 * @param {*} state 
	 * @param {object} permissions - the set of permissions as received from the API
	 */
	setPermissions(state, permissions) {
		state.permissions = permissions;
	}
	
};

export default {
	state,
	getters,
	mutations
};