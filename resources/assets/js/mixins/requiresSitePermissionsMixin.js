/**
 * Provides logic to help ensure necessary site permissions are set.
 * When the username or site changes it calls the permissions state's loadSiteRole with the site id from the route.
 * It make's the permission state's userSitePermissionsReady property available.
 *
 * NOTE: we avoid making calls to loadSiteRole if we have no token otherwise we run into a race condition due to the
 * api call with no token being replayed and returning a result *after* we've completed the subsequent successful call
 *
 * Components using this mixin should use a :v-if="userSitePermissionsReady" to determine whether or not they can
 * be displayed yet.
 */

import { mapGetters, mapActions } from 'vuex';

export default {
	mounted() {
		if (this.hasAPIToken) {
			this.loadSiteRole({
				siteId: this.siteId,
				username: this.username
			});
		}
	},

	watch: {
		username: function(newUsername) {
			// when username changes fetch site permissions
			if (this.hasAPIToken) {
				this.loadSiteRole({
					siteId: this.siteId,
					username: newUsername
				});
			}
		},

		siteId: function(newSiteId) {
			// when site changes fetch site permissions
			if (this.hasAPIToken) {
				this.loadSiteRole({
					siteId: newSiteId,
					username: this.username
				});
			}
		}
	},

	computed: {
		...mapGetters(['userSitePermissionsReady']),
		...mapGetters('auth', ['username', 'hasAPIToken']),

		siteId() {
			return this.$route.params.site_id || this.$route.params.siteId;
		}
	},

	methods: {
		...mapActions(['loadSiteRole'])
	}
};
