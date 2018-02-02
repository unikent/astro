/**
TopBar.vue

The top bar is the container for the main actions across the site and within pages.

The top bar is split into two flavours:

1. homepage. A very minimal top bar shown only on the homepage. It's made up of just a logout dropdown and other key site information.

2. page editing. The page editing top bar is made up of three sections:
a. back button to the site list/homepage
b. page title, publish status, and url
c. toolbar with device view, save, preview, publish actions, as well as the logout dropdown.

Note that the page editing toolbar is a separate component found in `components/Toolbar.vue`

*/
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

			<div v-if="showTools && publishStatus === 'new'" class="top-bar__page-title">
				<div class="top-bar__title">
					{{ pageTitle }}
					<el-tag type="primary">Unpublished</el-tag>
				</div>
				<span class="top-bar__url">{{ renderedURL }}</span>
			</div>

			<div v-else-if="showTools && publishStatus === 'draft'" class="top-bar__page-title">
				<div class="top-bar__title">
					{{ pageTitle }}
					<el-tag type="warning">Draft</el-tag>
				</div>
				<span v-if="!pageHasLayoutErrors" class="top-bar__url">
					<a :href="draftPreviewURL" target="_blank">{{ renderedURL }}</a> <icon name="newwindow" aria-hidden="true" width="12" height="12" class="ico" /></span>
			</div>

			<div v-else-if="showTools && publishStatus === 'published'" class="top-bar__page-title">
				<div class="top-bar__title">{{ pageTitle }}<el-tag type="success">Published</el-tag></div>
				<span class="top-bar__url"><a :href="publishedPreviewURL" target="_blank">{{ renderedURL }}</a> <icon name="newwindow" aria-hidden="true" width="12" height="12" class="ico" /></span>
			</div>

			<div v-else-if="showTools" class="top-bar__page-title">
				<div class="top-bar__title">{{ pageTitle }}</div>
				<span class="top-bar__url">{{ renderedURL }}</span>
			</div>

		</div>

		<div class="top-bar__tools">
			<toolbar v-if="showTools" />

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
	import { mapState, mapGetters, mapActions, mapMutations } from 'vuex';
	import Icon from 'components/Icon';
	import Toolbar from 'components/Toolbar';
	import promptToSave from '../mixins/promptToSave';
	import Config from '../classes/Config.js';

	/* global window */

	export default {

		name: 'top-bar',

		components: {
			Icon,
			Toolbar
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

			...mapGetters([
				'publishStatus',
				'pageTitle',
				'pageSlug',
				'pagePath',
				'sitePath',
				'siteDomain',
				'publishedPreviewURL',
				'draftPreviewURL'
			]),

			...mapState({
				layoutErrors: state => state.page.layoutErrors
			}),

			// works out if we should show a back button or not (ie whether we're editing a page or on the homepage)
			showBack() {
				return ['page'].indexOf(this.$route.name) !== -1;
			},

			showTools() {
				return ['site', 'page'].indexOf(this.$route.name) !== -1;
			},

			// lets us output the current user's username in the top bar
			username() {
				return window.astro.username;
			},

			// lets us output a calculated url for the current page in the top bar
			renderedURL() {
				return this.siteDomain + this.sitePath + this.pagePath;
			},

			config() {
				return Config;
			},

			siteTitle() {
				const site = this.sites.find(site => site.id === Number(this.$route.params.site_id));
				return site ? site.name : '';
			},

			pageHasLayoutErrors() {
				return this.layoutErrors.length !== 0;
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
				'handleSavePage',
				'loadPermissions',
				'loadGlobalRole'
			]),

			...mapMutations([
				'updateMenuActive'
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
					this.$router.push(`/site/${this.$route.params.site_id}`);
				})
			}
		}
	};
</script>
