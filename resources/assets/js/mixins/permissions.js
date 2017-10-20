/*
 * Mixin for permission checking
 */
import { mapGetters } from 'vuex';

export default {

	computed: {
		...mapGetters([
			'getRoles',
			'getCurrentRole'
		]),
	},

	methods: {

		/**
		 * @param {string} the permission to check i.e. subsite.edit, subsite.create
		 * @returns {bool} true or false
		 */
		canUser(permissionSlug) { 
			const roles = this.getRoles;
			const currentRole = this.getCurrentRole;
			let permitted = false;

			// if the user has the global role of admin then then they can do anything
			if (currentRole.global_role === 'admin') {
				return true;
			}
			const matchedRole = roles.find(function(value) {
				if (value.slug == this.permissionSlug) {
					return true;
				}
			}, {permissionSlug});
				
			if (matchedRole) {
				permitted = matchedRole.roles.includes(currentRole.role);
			}
			
			return permitted;   
		}
	}
};
