<template>
<div class="menu-editor" v-if="canUserEditMenu">
	<div class="columns">
		<div class="column">
			<el-card>
				<div slot="header" class="card__header">
					<span class="card__header-text">
						{{ this.site.title }} menu
						<el-tag
							:type="status === 'draft' ? 'primary' : 'success'"
							class="menu-status"
						>
							{{ status }} {{ isUnsaved ? '(unsaved)' : '' }}
						</el-tag>
						<el-tooltip v-if="lastPublishedDate" :content="datePublished" placement="bottom">
							<div class="menu-editor__publihed-date">
								Last published {{ timeElapsedSincePublish }}
							</div>
						</el-tooltip>
					</span>

					<div class="u-flex-auto-left">
						<el-button type="primary" @click="saveMenu">Save</el-button>
						<el-button type="primary" :plain="true" class="el-button--icon" @click="previewSite">
							Preview
							<icon name="newwindow" aria-hidden="true" width="12" height="12" class="ico" />
						</el-button>
						<el-button type="success" @click="publishMenu">Publish...</el-button>
					</div>
				</div>
				<draggable
					v-if="menu.length"
					v-model="menu"
					@change="updateErrors"
					:options="{
						handle: '.menu-editor-menu__item__drag-handle',
						chosenClass: 'menu-editor__menu-item--dragging'
					}"
				>
					<div v-for="(item, index) in menu" class="menu-editor__menu-item">
						<span class="menu-editor-menu__item__drag-handle">
							<icon name="arrow" />
						</span>

						<menu-item-field
							:item="item"
							:index="index"
							itemKey="text"
							name="Link text"
							placeholder="Homepage"
							:errors="errors"
							:validate="validateMenuItem"
						/>

						<menu-item-field
							:item="item"
							:index="index"
							itemKey="url"
							name="Location"
							placeholder="https://kent.ac.uk"
							:errors="errors"
							:validate="validateMenuItem"
						/>

						<span class="menu-item__cell u-flex-auto-left u-flex-grow-none">
							<el-button @click="removeMenuItem(index)" type="default" size="small">
								<icon name="delete" width="14" height="14" />
							</el-button>
						</span>
					</div>
				</draggable>
				<div v-else class="menu-editor__menu-item menu-editor__menu-item--empty">
					No menu items
				</div>

				<div class="menu-editor__footer">
					<el-button @click="addMenuItem" class="u-flex-auto-left">Add link</el-button>
				</div>
			</el-card>
		</div>
		<div class="column is-one-third">
			<site-page-links :on-click="addMenuItem" :site-pages="sitePages" />
		</div>
	</div>
</div>
<div class="menu-editor" v-else v-show="showPermissionsError">
	<el-alert
		title="You cannot edit the menu for this site"
		type="error"
		description="You do not have permission to edit the menu for this site. Please contact the site owner."
		:closable="false"
		show-icon
	>
  </el-alert>
</div>
</template>

<script>
/**
 * Example of the underlying data stored in the options object:
 *
 * Here we've removed all but one of the menu items from the draft version.
 *
 * {
 * 	"menu_draft": [
 * 		{
 * 			"text": "URL 1 text",
 * 			"url": "https://kent.ac.uk"
 * 		}
 * 	],
 * 	"menu_published": {
 * 		"links": [
 * 			{
 * 				"text": "URL 1 text",
 * 				"url": "https://kent.ac.uk"
 * 			},
 * 			{
 * 				"text": "URL 2 text",
 * 				"url": "https://kent.ac.uk"
 * 			}
 * 		],
 * 		"last_published": "Fri Oct 06 2017 12:00:00 GMT+0100 (GMT Summer Time)"
 * 	}
 * }
 */
import Schema from 'async-validator';
import Icon from 'components/Icon';
import Draggable from 'vuedraggable';
import ScrollInput from 'components/ScrollInput';
import MenuItemField from 'components/menu-editor/MenuItemField';
import SitePageLinks from 'components/menu-editor/SitePageLinks';
import { win, notify, prettyDate } from 'classes/helpers';
import Config from 'classes/Config';
import { mapGetters, mapActions } from 'vuex';
import permissions  from 'store/modules/permissions'; // this is to use canUser directly and provide our own state for the site

/* global setInterval, clearInterval */

export default {
	name: 'menu-editor',

	components: {
		Draggable,
		Icon,
		ScrollInput,
		SitePageLinks,
		MenuItemField
	},

	created() {
		this.fetchSiteData();

		this.validator = new Schema({
			text: [
				{
					type: 'string',
					required: true,
					message: 'Link text can\'t be  empty.'
				},
				{
					max: 100,
					message: 'Link text must be less than 100 characters.'
				}
			],
			url: [
				{
					required: true,
					message: 'Location can\'t be  empty.'
				},
				{
					type: 'url',
					message: 'Location must be a valid URL.'
				}
			]
		});

		this.updateTimeElapsedTimer = setInterval(
			this.updateTimeElapsedSincePublish,
			5000
		);
	},

	beforeRouteLeave(to, from, next) {
		if(this.isUnsaved) {
			this.$confirm(
				'Are you sure you want to leave?',
				'There are unsaved changes',
				{
					confirmButtonText: 'OK',
					cancelButtonText: 'Cancel',
					type: 'warning'
				}
			).then(() => {
				next();
			}).catch(() => {
				next(false);
			});
		}
		else {
			next();
		}
	},

	beforeDestroy() {
		clearInterval(this.updateTimeElapsedTimer);
	},

	data() {
		return {
			site: {
				firstPageId: 0,
				title: '',
				path: ''
			},

			menu: [],
			errors: [],
			sitePages: [],

			showPermissionsError: false, // to prevent the alert from flashing

			// slug of the user's current role on this site if they have one
			currentRole: '',

			// serialised version of the menu, to test equality
			// with current menu for isUnsaved computed property
			initialMenu: null,
			// serialised version of the published menu, to test equality
			// with current menu for status computed property
			publishedMenu: null,
			// raw published date as a string
			lastPublishedDate: null,
			// the time elapsed since ourlast publish, in a pretty format
			timeElapsedSincePublish: null
		};
	},

	computed: {

		...mapGetters([
			'getPermissions',
			'getGlobalRole'
		]),

		/**
		 * checks to see if the current user has the permissions to edit menus for this site
		 * @returns {boolean}
		 */
		canUserEditMenu() {
			let siteState = {};

			siteState.currentRole = this.currentRole;
			siteState.permissions = this.getPermissions;
			siteState.globalRole = this.getGlobalRole;
	
			return permissions.getters.canUser(siteState)('site.options.edit');
		},


		status() {
			return (
				// if unsaved but is the same as published menu, treat as draft
				JSON.stringify(this.menu) === this.publishedMenu &&
				!this.isUnsaved ?
					'published' : 'draft'
			);
		},

		isUnsaved() {
			return JSON.stringify(this.menu) !== this.initialMenu;
		},

		datePublished() {
			const date = new Date(this.lastPublishedDate);
			return `${date.toDateString()} at ${date.getHours()}:${date.getMinutes()}`
		}
	},

	methods: {

		fetchSiteData() {
			this.$api
				.get(`sites/${this.$route.params.site_id}?include=pages,role,users`)
				.then(({ data: json }) => {
					const publishedMenu = (
						json.data.options['menu_published'] ||
						{ links: null, last_published: null }
					);

					this.site = {
						firstPageId: json.data.pages[0].id,
						title: json.data.name,
						// TODO: don't hardcode HTTPS
						path: 'https://' + json.data.host + json.data.path,
						definedHost: json.data.host,
						definedPath: json.data.path
					};
					this.menu = json.data.options['menu_draft'] || [];
					this.initialMenu = JSON.stringify(this.menu);
					this.publishedMenu = JSON.stringify(publishedMenu.links);
					this.lastPublishedDate = publishedMenu.last_published;
					this.sitePages = json.data.pages;

					// store the current user's role for this site if they have one
					let siteUsers = json.data.users;
					if (siteUsers) {
						const currentRole = siteUsers.find((element) => element.username === window.astro.username);
						if (currentRole) {
							this.currentRole = currentRole.role;
						} 
					}
					
					// show the alert if needed
					if (!this.canUserEditMenu()) {
						this.showPermissionsError = true;
					}

					this.menu.forEach((item, i) => this.validateMenuItem(i));
					this.updateTimeElapsedSincePublish();
				});
		},

		validateMenuItem(index) {
			this.validator.validate(this.menu[index], (errors, fields) => {
				// if errors exist set them, otherwise set to null
				this.errors.splice(index, 1, errors ? fields : null);
			});
		},

		addMenuItem({ text, url } = { text: '', url: ''}) {
			const length = this.menu.push({
				text,
				url: url && url !== '' ? this.site.path + url : ''
			});

			this.validateMenuItem(length - 1);
		},

		removeMenuItem(index) {
			this.menu.splice(index, 1);
			this.errors.splice(index, 1);
		},

		hasErrors() {
			return this.errors.some((error) => error !== null);
		},

		saveMenu() {
			this.updateMenu('draft');
		},

		publishMenu() {
			if(this.hasErrors()) {
				notify({
					title: 'Menu not published',
					message: `
						There are some validation issues with this menu.
						These must be fixed before publishing it.
					`,
					type: 'error'
				});
			}
			else {
				this.updateMenu('published');
			}
		},

		updateMenu(updateType = 'draft') {
			const
				verb = updateType === 'draft' ? 'saved' : 'published',
				data = {
					options: {
						'menu_draft': this.menu
					}
				};

			if(updateType === 'published') {
				this.lastPublishedDate = new Date().toString();

				data.options['menu_published'] = {
					links: this.menu,
					last_published: this.lastPublishedDate
				};
			}

			this.$api
				.patch(`sites/${this.$route.params.site_id}`, data)
				.then(() => {

					if(updateType === 'published') {
						this.publishedMenu = JSON.stringify(this.menu);
						this.updateTimeElapsedSincePublish();
					}

					this.initialMenu = JSON.stringify(this.menu);

					const hasErrors = this.hasErrors();

					notify({
						title: `Menu ${verb}`,
						message: (
							hasErrors ? `
								The menu saved, but there are some validation errors.
								You won\'t be able to publish your menu until these are fixed.
							`
							: `Successfully ${verb} menu.`
						),
						type: hasErrors ? 'warning' : 'success'
					});
				})
				.catch(() => {
					notify({
						title: `Menu not ${verb}`,
						message: `
							An error was encountered, please try again later.
							If the problem persists contact your administrator.
						`,
						type: 'error'
					});
				});
		},

		previewSite() {
			win.open(
				`${Config.get('base_url', '')}/draft/${this.site.definedHost}${this.site.definedPath}`,
				'_blank'
			);
		},

		updateTimeElapsedSincePublish() {
			const newTime = prettyDate(new Date(this.lastPublishedDate).toString());

			if(this.timeElapsedSincePublish !== newTime) {
				this.timeElapsedSincePublish = newTime;
			}
		},

		updateErrors({ moved }) {
			if(moved) {
				const currentError = this.errors[moved.oldIndex];
				this.errors.splice(moved.oldIndex, 1);
				this.errors.splice(moved.newIndex, 0, currentError);
			}
		}
	}
};
</script>
