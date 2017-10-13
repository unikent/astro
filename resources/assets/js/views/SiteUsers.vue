<template>
<div class="site-users">
	<div class="columns">
		<div class="column">
			<el-card>
				<div slot="header" class="card__header">
					<span class="card__header-text">
						{{ this.site.title }} site users
						<!-- <el-tag
							:type="status === 'draft' ? 'primary' : 'success'"
							class="menu-status"
						>
							{{ status }} {{ isUnsaved ? '(unsaved)' : '' }}
						</el-tag> -->
					</span>

					<div class="u-flex-auto-left">
						<el-button type="primary" @click="saveUsers">Save</el-button>
					</div>
				</div>
				<template
					v-if="users.length"
					v-model="users"
				>
					<div class="el-table w100 el-table--fit el-table--striped el-table--border el-table--enable-row-hover">
						<table cellspacing="0" cellpadding="0" border="0" class="w100">
							<thead>
								<tr>
									<th>
										<div class="cell">
											User name
										</div>
									</th>
									<th>
										<div class="cell">
											Role
										</div>
									</th>
								</tr>
							</thead>
							<tbody v-loading.body="loading">
								<tr
								v-for="user in users"
								:key="user.id"
								class="el-table__row"
								>
									<td>
										<div class="cell">
											{{user.user.name}}
										</div>
									</td>
									<td>
										<div class="cell">
											{{user.user.role}}
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</template>
				<template v-else class="site-users__user site-users__user--empty">
					<div>No users</div>
				</template>
				<div class="site-users__footer">
					<el-button @click="addUser" class="u-flex-auto-left">Add user</el-button>
				</div>
			</el-card>
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
import Schema from 'async-validator';

import Icon from 'components/Icon';
import ScrollInput from 'components/ScrollInput';
import { win, readingSpeedFromString, prettyDate } from 'classes/helpers';
import Config from 'classes/Config';

/* global setInterval, clearInterval */

export default {
	name: 'site-users',

	components: {
		Icon,
		ScrollInput
	},

	created() {
		this.fetchSiteData();
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

	data() {
		return {
			site: {
				title: ''
			},

			users: [],
			errors: [],
			loading: true,

			// serialised version of the users, to test equality
			// with current users for isUnsaved computed property
			initialUsers: null
		};
	},

	computed: {
		isUnsaved() {
			return JSON.stringify(this.users) !== this.initialUsers;
		}
	},

	methods: {

		fetchSiteData() {
			this.$api
				.get(`sites/${this.$route.params.site_id}?include=users`)
				.then(({ data: json }) => {
					console.log(json);
					this.site = {
						title: json.data.name
					};
					this.users = json.data.users || [];
					this.initialUsers = JSON.stringify(this.users);
					this.loading = false;
				});
		},

		addUser({ username, role } = { username: '', role: ''}) {
			// const length = this.users.push({
			// 	user{
			// 		username: username,
			// 		role: role
			// 	}
			// });
		},

		removeUser(index) {
			this.users.splice(index, 1);
			this.errors.splice(index, 1);
		},

		hasErrors() {
			return this.errors.some((error) => error !== null);
		},

		saveUsers() {
			this.updateUsers();
		},

		updateUsers() {
			// const
			// 	verb = updateType === 'draft' ? 'saved' : 'published',
			// 	data = {
			// 		options: {
			// 			'menu_draft': this.users
			// 		}
			// 	};

			// if(updateType === 'published') {
			// 	this.lastPublishedDate = new Date().toString();

			// 	data.options['menu_published'] = {
			// 		links: this.users,
			// 		last_published: this.lastPublishedDate
			// 	};
			// }

			// this.$api
			// 	.patch(`sites/${this.$route.params.site_id}`, data)
			// 	.then(() => {

			// 		if(updateType === 'published') {
			// 			this.publishedMenu = JSON.stringify(this.users);
			// 			this.updateTimeElapsedSincePublish();
			// 		}

			// 		this.initialUsers = JSON.stringify(this.users);

			// 		const hasErrors = this.hasErrors();

			// 		this.notify({
			// 			title: `Menu ${verb}`,
			// 			message: (
			// 				hasErrors ? `
			// 					The menu saved, but there are some validation errors.
			// 					You won\'t be able to publish your menu until these are fixed.
			// 				`
			// 				: `Successfully ${verb} menu.`
			// 			),
			// 			type: hasErrors ? 'warning' : 'success'
			// 		});
			// 	})
			// 	.catch(() => {
			// 		this.notify({
			// 			title: `Menu not ${verb}`,
			// 			message: `
			// 				An error was encountered, please try again later.
			// 				If the problem persists contact your administrator.
			// 			`,
			// 			type: 'error'
			// 		});
			// 	});
		},

		notify({ title, message, type }) {
			this.$notify({
				title,
				message,
				type,
				duration: readingSpeedFromString(message, 3000),
				onClick() {
					this.close();
				}
			});
		}
	}
};
</script>
