/**
 * Provides logic to help ensure necessary site permissions are ready before this component does anything.
 * When created it calls the permissions state's loadSiteRole with the site id from the route.
 * It make's the permission state's userSitePermissionsReady property available.
 *
 * Components using this mixin should use a :v-if="userSitePermissionsReady" to determine whether or not they can
 * be displayed yet.
 */

import { mapGetters, mapActions } from 'vuex';
import Config from 'classes/Config';

export default {

	created() {
		this.loadSiteRole({
			siteId: this.$route.params.site_id || this.$route.params.siteId,
			username: Config.get('username')
		});
	},

	computed: {
		...mapGetters(['userSitePermissionsReady'])
	},

	methods: {
		...mapActions([
			'loadSiteRole'
		]),
	}
};
