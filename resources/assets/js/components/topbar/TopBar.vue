<template>
	<div class="top-bar">
		<div>

			<div
				v-if="(!showBack && this.$route.name !== 'site-list') && sites.length > 1"
			>
				<el-popover
					ref="site-picker"
					placement="bottom-start"
					v-model="siteDropdownVisible"
					transition="el-zoom-in-top"
				>
					<ul class="site-picker">
						<template v-for="(n, i) in Math.min(sites.length, 11)">
							<li v-if="n !== 11">
								<router-link :to="`/site/${sites[i].id}`" @click.native="siteDropdownVisible = false">
									{{ sites[i].name }}
								</router-link>
							</li>
							<li v-else>
								More sites available...
							</li>
						</template>

						<li>
							<router-link to="/" @click.native="siteDropdownVisible = false">
								<i class="el-icon-arrow-left"></i> Back to sites
							</router-link>
						</li>
					</ul>
				</el-popover>

				<span v-popover:site-picker class="site-pick">
					<icon name="site" />
					{{ siteTitle }}<i class="el-icon-caret-bottom el-icon--right"></i>
				</span>
			</div>
			<div
				v-if="(!showBack && this.$route.name !== 'site-list') && sites.length === 1"
				 class="site-pick"
			>
				<icon name="site" /> {{ siteTitle }}
			</div>

			<div v-show="showBack" @click="backToAdmin" class="top-bar-backbutton">
				<i class="el-icon-arrow-left backbutton-icon"></i>Back
			</div>

			<slot name="title" />

		</div>

		<div class="top-bar__tools">
			<slot name="tools" v-if="showTools" />

			<el-popover
				ref="user-dropdown"
				placement="bottom-end"
				v-model="accountDropdownVisible"
				transition="el-zoom-in-top"
				popper-class="user-account-dropdown"
			>
				<div>
					<div class="user-account-dropdown__item">
						Signed in as <strong>{{ username }}</strong>
					</div>
					<div class="user-account-dropdown__item user-account-dropdown__item--divided">Settings</div>
					<div @click="signOut" class="user-account-dropdown__item user-account-dropdown__item--clickable">
						<form ref="submit-form" method="post" :action="`${config.get('base_url', '')}/auth/logout`">
							<input type="hidden" name="_token" :value="config.get('csrf_token')" />
						</form>
						<span>Sign out</span>
					</div>
				</div>
			</el-popover>

			<span v-popover:user-dropdown class="user-account-button">
				Account<i class="el-icon-caret-bottom el-icon--right"></i>
			</span>
		</div>
	</div>
</template>

<script>
	import { mapActions } from 'vuex';
	import Icon from 'components/Icon';
	import promptToSave from 'mixins/promptToSave';
	import Config from 'classes/Config.js';

	/* global window */

	export default {

		name: 'top-bar',

		props: ['site-id'],

		components: {
			Icon
		},

		mixins: [promptToSave],

		created() {
			this.fetchSiteData();
			// refresh our site list dropdown when a new site is added
			// TODO: replace with more structured state, rather than an event
			this.$bus.$on('top-bar:fetchSitData', this.fetchSiteData);
			window.addEventListener('beforeunload', this.leaveAstro);
		},

		mounted() {
			this.loadPermissions();
			this.loadGlobalRole(window.astro.username);
			this.$store.dispatch('site/fetchLayouts');
			this.$store.dispatch('site/fetchSiteDefinitions');
		},

		destroyed() {
			this.$bus.$off('top-bar:fetchSitData');
		},

		data() {
			return {
				siteDropdownVisible: false,
				accountDropdownVisible: false,
				sites: []
			};
		},

		computed: {

			// works out if we should show a back button or not (ie whether we're editing a page or on the homepage)
			showBack() {
				return ['page', 'profile-editor'].indexOf(this.$route.name) !== -1;
			},

			showTools() {
				return ['site', 'page', 'profile-editor'].indexOf(this.$route.name) !== -1;
			},

			// lets us output the current user's username in the top bar
			username() {
				return window.astro.username;
			},

			config() {
				return Config;
			},

			siteTitle() {
				const site = this.sites.find(site => site.id === Number(this.$route.params.site_id));
				return site ? site.name : '';
			}
		},

		methods: {

			leaveAstro(e) {
				/* we are very limited as to what we can do when someone tries to leave
				 https://developer.mozilla.org/en/docs/Web/Events/beforeunload
				 */
				const unsavedChangesExist = this.unsavedChangesExist();
				if (unsavedChangesExist) {
					var confirmationMessage = 'You may lose changes if you leave without saving';
					e.returnValue = confirmationMessage;     // Gecko, Trident, Chrome 34+
					return confirmationMessage;              // Gecko, WebKit, Chrome <34
				}
			},

			...mapActions([
				'loadPermissions',
				'loadGlobalRole'
			]),

			fetchSiteData() {
				this.$api
					.get('sites')
					.then(({ data: json }) => {
						this.sites = json.data;
					});
			},

			signOut() {
				this.promptToSave(() => {
					this.$refs['submit-form'].submit();
				});
			},

			/**
			 gets the user back to the main admin area
			 */
			backToAdmin() {
				// another prompt to save the page when going back to the site listing
				this.promptToSave(() => {
					this.$store.commit('setLoaded', false);
					this.$store.commit('setPage', {});
					this.$router.push(`/site/${this.siteId || this.$route.params.site_id}`);
				})
			}
		}
	};
</script>
