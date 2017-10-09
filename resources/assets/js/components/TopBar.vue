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
	<div class="top-bar" :class="{ 'top-bar--homepage' : !showBack }">
		<div>
			<div v-show="showBack" @click="backToSites" class="top-bar-backbutton">
				<i class="el-icon-arrow-left backbutton-icon"></i>Sites
			</div>

			<div v-if="showTools && publishStatus === 'new'" class="top-bar__page-title">
				<div class="top-bar__title">{{ pageTitle }}<el-tag type="primary">Unpublished draft</el-tag></div>
				<span class="top-bar__url">{{ renderedURL }}</span>
			</div>

			<div v-else-if="showTools && publishStatus === 'published'" class="top-bar__page-title">
				<div class="top-bar__title">{{ pageTitle }}<el-tag type="success">Published</el-tag></div>
				<span class="top-bar__url"><a :href="renderedURL" target="_blank">{{ renderedURL }}</a> <icon name="newwindow" aria-hidden="true" width="12" height="12" class="ico" /></span>
			</div>

			<div v-else-if="showTools && publishStatus === 'draft'" class="top-bar__page-title">
				<div class="top-bar__title">{{ pageTitle }}<el-tag type="warning">Draft</el-tag></div>
				<span class="top-bar__url"><a :href="renderedURL" target="_blank">{{ renderedURL }}</a> <icon name="newwindow" aria-hidden="true" width="12" height="12" class="ico" /></span>
			</div>

			<div v-else-if="showTools" class="top-bar__page-title">
				<div class="top-bar__title">{{ pageTitle }}</div>
				<span class="top-bar__url">{{ renderedURL }}</span>
			</div>

		</div>

		<div class="top-bar__tools">
			<toolbar v-if="showTools" />

			<el-dropdown trigger="click" @command="handleCommand" class="user-menu-button">
				<span class="el-dropdown-link">
					{{ username }}<i class="el-icon-caret-bottom el-icon--right"></i>
				</span>
				<el-dropdown-menu slot="dropdown">
					<el-dropdown-item command="sign-out">Sign out</el-dropdown-item>
				</el-dropdown-menu>
			</el-dropdown>
		</div>
	</div>
</template>

<script>
	import { mapGetters, mapActions, mapMutations } from 'vuex';
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

		mixins:[promptToSave],

		created() {
			window.addEventListener('beforeunload', this.leaveAstro);
		},

		computed: {

			...mapGetters([
				'publishStatus',
				'pageTitle',
				'pageSlug',
				'pagePath',
				'sitePath',
				'siteDomain'
			]),

			// works out if we should show a back button or not (ie whether we're editing a page or on the homepage)
			showBack() {
				return ['site', 'page', 'menu-editor'].indexOf(this.$route.name) !== -1;
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
			}
		},

		methods: {

			leaveAstro(e) {
				/* we are very limited as to what we can do when someone tries to leave
				 https://developer.mozilla.org/en/docs/Web/Events/beforeunload
				 */
				const unsavedChangesExist = this.unsavedChangesExist();
				if (unsavedChangesExist == true) {
					var confirmationMessage = "You may lose changes if you leave without saving";
					e.returnValue = confirmationMessage;     // Gecko, Trident, Chrome 34+
					return confirmationMessage;              // Gecko, WebKit, Chrome <34
				}
			},

			...mapActions([
				'handleSavePage'
			]),

			...mapMutations([
				'updateMenuActive'
			]),

			handleCommand(command) {
				if(command === 'sign-out') {
					// prompts the user to save the page when they try to log out
					this.promptToSave(() => {
						const form = document.createElement('form');
						form.setAttribute('method', 'post');
						form.setAttribute('action', Config.get('base_url', '') + '/auth/logout');
						const csrf = document.createElement('input');
						csrf.setAttribute('type', 'hidden');
						csrf.setAttribute('name', '_token');
						csrf.setAttribute('value', window.astro.csrf_token);
						form.appendChild(csrf);
						document.body.appendChild(form);
						form.submit();
						//window.location = Config.get('base_url', '') + '/auth/logout';
					});
				}
			},

			/**
			 gets the user back to the main site listing
			 */
			backToSites() {
				// another prompt to save the page when going back to the site listing
				this.promptToSave(() => {
					this.$store.commit('setLoaded', false);
					this.$store.commit('setPage', {});
					this.$router.push('/sites');
				})
			}
		}
	};
</script>
