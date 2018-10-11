<template>
<div class="site-users" v-if="canUserManageUsers">
	<el-card>
		<div slot="header" class="card__header">
			<span class="card__header-text">
				{{ this.siteTitle }} site users
			</span>
		</div>

		<h3>Add new users to {{ this.siteTitle }}</h3>

		<div class="add-user">

			<div
				class="add-user__select"
				:class="{ 'is-error--custom' : errors.usersToAdd }">
				<custom-multi-select
					v-model="usersToAdd"
					:items="filteredUserList"
					label-path="name"
					value-path="username"
					key-path="id"
					:filter-callback="filterUserList"
					placeholder="Search for a user to add"
					no-data-text="No more users to add"
					no-match-text="No users matching your query"
					class="add-user__multiselect"
				>
					<template slot="item" slot-scope="props">
						<span>{{ props.item.name }}</span>
						<span class="add-user-multiselect__username">
							@{{ props.item.username }}
						</span>
					</template>
				</custom-multi-select>
				<div v-if="errors.usersToAdd" class="el-form-item__error">
					{{ errors.usersToAdd }}

				</div>
			</div>

			<div
				class="u-flex-auto-left add-user__role-select"
				:class="{ 'is-error--custom' : errors.selectedRole }"
			>
				<el-select v-model="selectedRole" placeholder="Select role">
					<el-option v-for="role in roles"
						:label="role.name"
						:value="role.slug"
						:key="role.slug"
					/>
				</el-select>
				<div v-if="errors.selectedRole" class="el-form-item__error">
					{{ errors.selectedRole }}
				</div>
			</div>

			<el-button
				@click="addUsers"
				type="primary"
				class="u-flex-auto-left add-user__add-button"
			>
				Add user{{ multipleUsersToAdd ? 's' : ''}}
			</el-button>
		</div>

		<el-row v-if="add_user_note">
			<el-col :xs="24" :sm="22" :md="20" :lg="16" :xl="16"><p class="add-user-note color-text-secondary" v-html="add_user_note"></p></el-col>
		</el-row>

		<h3>Existing users</h3>

		<div class="filter-user">
			<el-input
				v-model="searchInput"
				placeholder="Find users"
				suffix-icon="el-icon-search"
				class="filter-user__searchbox"
			/>
			<el-select
				v-model="roleFilter"
				class="u-flex-auto-left filter-user__select"
				placeholder="Role"
			>
				<el-option
					label="Filter by role"
					:value="null"
				/>
				<el-option-group label="Filter by role">
					<el-option v-for="role in roles"
						:label="role.name"
						:value="role.slug"
						:key="role.slug"
					/>
				</el-option-group>
			</el-select>
		</div>

		<div v-if="users.length">
			<div class="el-table w100 el-table--fit el-table--striped el-table--border el-table--enable-row-hover">
				<table cellspacing="0" cellpadding="0" border="0" class="w100">
					<thead>
						<tr>
							<th>
								<div class="cell">
									Name
								</div>
							</th>
							<th>
								<div class="cell">
									Username
								</div>
							</th>
							<th>
								<div class="cell">
									Email
								</div>
							</th>
							<th>
								<div class="cell">
									Role
								</div>
							</th>
							<th>
								<div class="cell">
									Actions
								</div>
							</th>
						</tr>
					</thead>
					<tbody>
						<template v-if="pagedUsers.length">
							<tr
								v-for="(user, index) in pagedUsers"
								class="el-table__row"
							>
								<td>
									<div class="cell">
										{{ user.name }}
									</div>
								</td>
								<td>
									<div class="cell">
										{{ user.username }}
									</div>
								</td>
								<td>
									<div class="cell">
										{{ user.email }}
									</div>
								</td>
								<td>
									<div class="cell">
										<el-select
											v-model="user.role"
											size="small"
											class="u-flex-auto-left"
											@change="(roleSlug) => changeUserRole(user, roleSlug)"
										>
											<el-option-group label="Change role">
												<el-option v-for="role in roles"
													:label="role.name"
													:value="role.slug"
													:key="role.slug"
												/>
											</el-option-group>
										</el-select>
									</div>
								</td>
								<td>
									<div class="cell">
									<el-button
										@click="removeUser(user)"
										type="default"
										size="small"
									>
										<icon name="delete" :width="14" :height="14" />
									</el-button>
									</div>
								</td>
							</tr>
						</template>
						<template v-else>
							<tr>
								<td colspan="5" align="center"><div class="cell">No users matching your query.</div></td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>

			<el-pagination
				@size-change="handleCountChange"
				@current-change="handlePagination"
				:current-page="currentPage"
				:page-sizes="counts"
				:page-size="count"
				layout="slot, sizes, ->, prev, pager, next"
				:total="total"
			>
				<slot>
					<span class="show-text">Show</span>
				</slot>
			</el-pagination>
		</div>
		<div v-else class="site-users__user--empty">
			No existing users to display.
		</div>
	</el-card>
</div>
<div class="menu-editor" v-else v-show="showPermissionsError">
	<el-alert
		title="You cannot manage users for this site"
		type="error"
		description="You do not have permission to manage users for this site. Please contact the site owner."
		:closable="false"
		show-icon
	/>
</div>
</template>

<script>
import Schema from 'async-validator';
import _ from 'lodash';

import Icon from 'components/Icon';
import CustomMultiSelect from 'components/CustomMultiSelect';
import { notify, aOrAn } from 'classes/helpers';
import { mapGetters } from 'vuex';
import permissions  from 'store/modules/permissions'; // this is to use canUser directly and provide our own state for the site
import Config from 'classes/Config';

export default {

	name: 'site-users',

	props: {
		counts: {
			type: Array,
			default: () => [20, 50, 100, 200]
		}
	},

	components: {
		Icon,
		CustomMultiSelect
	},

	created() {
		this.fetchSiteData();

		this.validator = new Schema({
			usersToAdd: {
				type: 'array',
				required: true,
				min: 1,
				message: 'Please select a user to add'
			},
			selectedRole: {
				type: 'string',
				required: true,
				enum: this.roles,
				message: 'Please select a role'
			}
		});
	},

	data() {
		return {
			siteTitle: '',
			roleFilter: null,
			searchInput: '',

			users: [],

			roles: [],

			showPermissionsError: false, // to prevent the alert from flashing on load

			// slug of the user's current role on this site if they have one
			currentRole: '',

			currentPage: 1,
			count: 20,

			userList: [],
			usersToAdd: [],
			selectedRole: '',
			errors: {
				usersToAdd: null,
				selectedRole: null
			}
		};
	},

	computed: {

		...mapGetters([
			'getPermissions',
			'getGlobalRole'
		]),

		...mapGetters('auth', [
			'username'
		]),

		canUserManageUsers() {

			let siteState = {};

			siteState.currentRole = this.currentRole;
			siteState.permissions = this.getPermissions;
			siteState.globalRole = this.getGlobalRole;

			return permissions.getters.canUser(siteState)('permissions.site.assign');
		},

		filteredUsers() {
			const users = this.roleFilter ?
				this.users.filter(user => user.role === this.roleFilter) :
				this.users;

			return (
				this.searchInput.length > 1 ?
					users.filter(
						this.createFilter(
							['name', 'username', 'email'],
							this.searchInput.toLowerCase()
						)
					) :
					users
			);
		},

		filteredUserList() {
			return _.differenceBy(this.userList, this.users, 'name');
		},

		multipleUsersToAdd() {
			return this.usersToAdd.length > 1;
		},

		pagedUsers() {
			const
				from = (this.currentPage - 1) * this.count,
				to = from + this.count;

			return (
				this.total < (to - from) ?
					this.filteredUsers : this.filteredUsers.slice(from, to)
			);
		},

		total() {
			return this.filteredUsers.length;
		},

		add_user_note() {
			return Config.get('add_user_note');
		}

	},

	methods: {
		fetchSiteData() {
			const fetchUserList = this.$api.get('users');
			const fetchSite = this.$api.get(`sites/${this.$route.params.site_id}?include=users`);
			const fetchRoles = this.$api.get('roles');

			this.$api
				.all([fetchSite, fetchUserList, fetchRoles])
				// TODO: catch errors
				.then(this.$api.spread((site, users, roles) => {
					this.siteTitle = site.data.data.name;
					this.users = site.data.data.users || [];
					this.userList = users.data.data || [];
					this.roles = roles.data.data || [];

					const currentRole = this.users.find(
						(user) => user.username === this.username
					);

					if(currentRole) {
						this.currentRole = currentRole.role;
					}

					// show the alert if needed
					if (!this.canUserManageUsers) {
						this.showPermissionsError = true;
					}
				}));
		},

		createFilter(searchKeys, searchTerm) {
			return user => searchKeys.some(
				key => (user[key].toLowerCase().indexOf(searchTerm) !== -1)
			);
		},

		filterUserList(item, searchTerm) {
			return ['name', 'username', 'email'].some(
				key => item[key].toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1
			);
		},

		addUsers() {
			this.validator.validate(this, (errors, fields) => {
				Object.keys(this.errors).forEach(key => {
					this.errors[key] = (
						fields && fields[key] ?
							fields[key][0].message : null
					);
				});

				if(!errors) {
					const requests = this.usersToAdd.map(username => this.$api
						.put(
							`sites/${this.$route.params.site_id}/users`, {
								username,
								role: this.selectedRole
							}
						)
						.catch((error) => error.response)
					);

					// TODO: catch errors
					this.$api
						.all(requests)
						.then(response => {
							let lastResponse = {}, erroredUsers = [];

							for (let i = 0; i < response.length; i++) {
								if(response[i].status === 200) {
									lastResponse = response[i];
								}
								else {
									erroredUsers.push(JSON.parse(response[i].config.data).username);
								}
							}

							if(Object.keys(lastResponse).length !== 0) {

								this.users = lastResponse.data.data.users || [];
								this.resetFilters();

								notify({
									title: 'Users added successfully',
									type: 'success'
								});
							}

							if (erroredUsers.length > 0) {
								notify({
									title: 'Unable to add users',
									message: `Adding the following users was unsuccessful: ${erroredUsers.join(', ')}`,
									type: 'error'
								});
							}

						});
				}
			});
		},

		removeUser({ name, username }) {
			this
				.$confirm(`Are you sure you want to remove ${name} from this site?`, 'Warning', {
					confirmButtonText: 'OK',
					cancelButtonText: 'Cancel',
					type: 'warning'
				})
				// TODO: catch
				.then(() => {
					this.$api
						.put(
							`sites/${this.$route.params.site_id}/users`,
							{ username }
						)
						.then(({ data: json }) => {
							this.users = json.data.users || [];

							notify({
								title: 'User access successfully removed',
								message: `${name} no longer has access to this site.`,
								type: 'success'
							});
						})
						.catch(() => {
							notify({
								title: 'Unable to remove user',
								type: 'error'
							});
						});
				});
		},

		handleCountChange(newSize) {
			this.count = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		},

		resetFilters() {
			this.usersToAdd = [];
			this.selectedRole = '';
			this.errors = {
				usersToAdd: null,
				selectedRole: null
			};
		},

		changeUserRole({ name, username }, roleSlug) {
			this.$api
				.put(
					`sites/${this.$route.params.site_id}/users`, {
						username,
						role: roleSlug
					}
				)
				.then(({ data: json }) => {
					const roleName = this.roles.find(role => role.slug === roleSlug).name;
					this.users = json.data.users || [];

					notify({
						title: 'Role successfully changed',
						message: `${name} is now ${aOrAn(roleName)} ${roleName}`,
						type: 'success'
					});
				})
				.catch(() => {
					notify({
						title: "Unable to change user's role",
						type: 'error'
					});
				});
		}
	}
};
</script>