<template>
<div class="menu-editor">
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
							<div style="font-size: 12px; text-transform: none; margin-top: 4px;">
								Last published {{ timeElapsedSincePublish }}
							</div>
						</el-tooltip>
					</span>

					<div style="margin-left: auto">
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
					:options="{
						handle: '.page-list__item__drag-handle'
					}"
				>
					<div v-for="(item, index) in menu" class="menu-editor__menu-item">
						<span class="page-list__item__drag-handle" style="line-height: 50px;">
							<icon name="arrow" />
						</span>

						<menu-item-field
							:item="item"
							:index="index"
							nestedKey="text"
							name="Link text"
							placeholder="Homepage"
							:errors="errors"
							:validate="validateMenuItem"
						/>

						<menu-item-field
							:item="item"
							:index="index"
							nestedKey="url"
							name="Location"
							placeholder="https://kent.ac.uk"
							:errors="errors"
							:validate="validateMenuItem"
						/>

						<span class="menu-item__cell" style="margin-left: auto; flex-grow: 0;">
							<el-button @click="removeMenuItem(index)" type="default" size="small">
								<icon name="delete" width="14" height="14" />
							</el-button>
						</span>
					</div>
				</draggable>
				<div v-else class="menu-editor__menu-item">
					No menu items
				</div>

				<div style="display: flex; align-items: center;">
					<el-button @click="addMenuItem" style="margin-left: auto;">Add link</el-button>
				</div>
			</el-card>
		</div>
		<div class="column is-one-third">
			<site-page-links :on-click="addMenuItem" :site-pages="sitePages" />
		</div>
	</div>
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
import Vue from 'vue';
import Schema from 'async-validator';

import Icon from 'components/Icon';
import Draggable from 'vuedraggable';
import ScrollInput from 'components/ScrollInput';
import MenuItemField from 'components/menu-editor/MenuItemField';
import SitePageLinks from 'components/menu-editor/SitePageLinks';
import { readingSpeedFromString, prettyDate } from 'classes/helpers';

/* global setInterval, clearInterval, window */

const vue = new Vue();

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
				.get(`sites/${this.$route.params.site_id}?include=pages`)
				.then(({ data: json }) => {
					this.site = {
						firstPageId: json.data.pages[0].id,
						title: json.data.name,
						// TODO: don't hardcode HTTPS
						path: 'https://' + json.data.host + json.data.path,
					};

					this.menu = json.data.options['menu_draft'] || [];
					this.initialMenu = JSON.stringify(this.menu);

					const publishedMenu = (
						json.data.options['menu_published'] ||
						{ links: null, last_published: null }
					);

					this.lastPublishedDate = publishedMenu.last_published;
					this.updateTimeElapsedSincePublish();

					this.publishedMenu = JSON.stringify(publishedMenu.links);

					this.menu.forEach((item, i) => this.validateMenuItem(i));

					this.sitePages = json.data.pages;
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

		validateMenuItem(index) {
			this.validator.validate(this.menu[index], (errors, fields) => {
				if(errors) {
					this.errors.splice(index, 1, fields);
				}
				else {
					this.errors[index] = null;
				}
			});
		},

		hasErrors() {
			return this.errors.some((error) => error !== null);
		},

		saveMenu() {
			let message, type;

			if(this.hasErrors()) {
				message = vue.$createElement(
					'div',
					{
						'style': {
							color: '#bb9132'
						},
					},
					[
						vue.$createElement(
							'p',
							`
							The menu saved, but there are some validation errors.
							You won\'t be able to publish your menu until these are fixed.
							`
						)
					]
				);
				type = 'warning';

				this.$notify({
					title: 'Menu saved',
					message,
					type,
					duration: 5000,
					onClick() {
						this.close();
					}
				});
			}

			this.updateMenu('draft');
		},

		publishMenu() {
			if(this.hasErrors()) {
				this.$snackbar.open({
					message: 'There are some errors y\'all.'
				});
			}
			else {
				this.updateMenu('published');
			}
		},

		updateMenu(updateType = 'draft') {
			let message, type;

			const verb = updateType === 'draft' ? 'save' : 'publish';

			const data = {
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

					message = `Successfully ${verb}d menu.`;
					type = 'success';

					this.$notify({
						title: `Menu ${verb}d`,
						message,
						type,
						duration: readingSpeedFromString(message, 2000),
						onClick() {
							this.close();
						}
					});
				})
				.catch(() => {
					message = `Couldn't ${verb} menu.`;
					type = 'warning';

					this.$notify({
						title: `Menu ${verb}d`,
						message,
						type,
						duration: readingSpeedFromString(message, 2000),
						onClick() {
							this.close();
						}
					});
				});
		},

		previewSite() {
			window.open(
				`${window.astro.base_url}/draft/${this.site.firstPageId}`,
				'_blank'
			);
		},

		updateTimeElapsedSincePublish() {
			const newTime = prettyDate(new Date(this.lastPublishedDate).toString());

			if(this.timeElapsedSincePublish !== newTime) {
				this.timeElapsedSincePublish = newTime;
			}
		}
	}
};
</script>
