<template>
<div class="site-users">
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
					:items="userList"
					label-path="name"
					value-path="id"
					key-path="id"
					:filter-callback="filterUserList"
					placeholder="Search for a user"
					class="add-user__multiselect"
				>
					<template slot="item" scope="props">
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

		<h3>Existing users</h3>

		<div
			v-if="pagedUsers.length"
			class="el-table w100 el-table--fit el-table--striped el-table--border el-table--enable-row-hover"
		>
			<div class="filter-user">
				<el-input
					v-model="searchInput"
					placeholder="Find users"
					icon="search"
					class="filter-user__searchbox"
				/>
				<el-select
					v-model="roleFilter"
					class="u-flex-auto-left filter-user__select"
					placeholder="Role"
				>
					<el-option
						label="No filter"
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
				<tbody v-loading.body="loading">
					<tr
						v-for="user in pagedUsers"
						:key="user.id"
						class="el-table__row"
					>
						<td>
							<div class="cell">
								{{ user.user.name }}
							</div>
						</td>
						<td>
							<div class="cell">
								{{ user.user.username }}
							</div>
						</td>
						<td>
							<div class="cell">
								{{ user.user.email }}
							</div>
						</td>
						<td>
							<div class="cell">
								<el-select
									v-model="user.role"
									size="small"
									class="u-flex-auto-left"
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
								@click="removeUser(user.id)"
								type="default"
								size="small"
							>
								<icon name="delete" width="14" height="14" />
							</el-button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>

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
</template>

<script>
import Schema from 'async-validator';

import Icon from 'components/Icon';
import CustomMultiSelect from 'components/CustomMultiSelect';
import { notify } from 'classes/helpers';

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

			currentPage: 1,
			count: 20,
			loading: true,

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

		searchFilter() {
			return this.searchInput.length > 2 ? this.searchInput : null;
		},

		filteredUsers() {
			const users = this.roleFilter ?
				this.users.filter(user => user.role === this.roleFilter) :
				this.users;

			return (
				this.searchFilter !== null ?
					users.filter(
						this.createFilter(
							['name', 'username', 'email'],
							this.searchFilter.toLowerCase()
						)
					) :
					users
			);
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
		}

	},

	methods: {
		fetchSiteData() {
			const fetchUserList = this.$api.get(`users`);
			const fetchSite = this.$api.get(`sites/${this.$route.params.site_id}?include=users`);
			const fetchRoles = this.$api.get('roles');

			this.$api
				.all([fetchSite, fetchUserList, fetchRoles])
				.then(this.$api.spread((site, users, roles) => {
					this.siteTitle = site.data.data.name;
					this.users = site.data.data.users || [];
					this.userList = users.data.data || [];
					this.roles = roles.data.data || [];
					this.loading = false;
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
					this.$api
						.post(
							`sites/${this.$route.params.site_id}/users`,
							this.usersToAdd.map(username => ({
								username,
								role: this.selectedRole
							}))
						)
						.then(({ data: json }) => {
							this.users = json.data.users || [];
							this.resetFilters();

							notify({
								title: 'Users added successfully',
								type: 'sucess'
							});
						})
						.catch(() => {
							this.resetFilters();

							notify({
								title: 'Users added successfully',
								type: 'success'
							});
						});
				}
			});
		},

		removeUser(index) {
			this.users.splice(index, 1);
		},

		handleCountChange(newSize) {
			this.count = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		},

		resetFilters(){
			this.handlePagination(0);
			this.searchInput = '';
			this.roleFilter = null;
			this.usersToAdd = [];
			this.selectedRole = '';
			this.errors = {
				usersToAdd: null,
				selectedRole: null
			};
		}
	}
};
</script>