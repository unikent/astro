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
					label-path="user.name"
					value-path="user.id"
					key-path="user.id"
					placeholder="Search for a user"
					class="add-user__multiselect"
				>
					<template slot="item" scope="props">
						<span>{{ props.item.user.name }}</span>
						<span class="add-user-multiselect__username">
							@{{ props.item.user.username }}
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
						:label="role"
						:value="role"
						:key="role"
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
							:label="role"
							:value="role"
							:key="role"
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
											:label="role"
											:value="role"
											:key="role"
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

			roles: [
				'Site Owner',
				'Editor',
				'Contributor'
			],

			currentPage: 1,
			count: 20,
			loading: true,

			userList: [
  {
    "user": {
      "name": "Trudy Mclean",
      "email": "trudymclean@quintity.com",
      "username": "user1",
      "id": 1
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Terry Bentley",
      "email": "terrybentley@quintity.com",
      "username": "user2",
      "id": 2
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Josefina Jenkins",
      "email": "josefinajenkins@quintity.com",
      "username": "user3",
      "id": 3
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Zamora Estes",
      "email": "zamoraestes@quintity.com",
      "username": "user4",
      "id": 4
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Carissa Clay",
      "email": "carissaclay@quintity.com",
      "username": "user5",
      "id": 5
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Figueroa Gay",
      "email": "figueroagay@quintity.com",
      "username": "user6",
      "id": 6
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Avis Sharp",
      "email": "avissharp@quintity.com",
      "username": "user7",
      "id": 7
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Nicholson Mack",
      "email": "nicholsonmack@quintity.com",
      "username": "user8",
      "id": 8
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Geraldine Clayton",
      "email": "geraldineclayton@quintity.com",
      "username": "user9",
      "id": 9
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Fitzpatrick Higgins",
      "email": "fitzpatrickhiggins@quintity.com",
      "username": "user10",
      "id": 10
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Madeleine Ross",
      "email": "madeleineross@quintity.com",
      "username": "user11",
      "id": 11
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Rhoda Hooper",
      "email": "rhodahooper@quintity.com",
      "username": "user12",
      "id": 12
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Patel Walsh",
      "email": "patelwalsh@quintity.com",
      "username": "user13",
      "id": 13
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Annie Rodriquez",
      "email": "annierodriquez@quintity.com",
      "username": "user14",
      "id": 14
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Pickett Rich",
      "email": "pickettrich@quintity.com",
      "username": "user15",
      "id": 15
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Petersen Morin",
      "email": "petersenmorin@quintity.com",
      "username": "user16",
      "id": 16
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Gallegos Spencer",
      "email": "gallegosspencer@quintity.com",
      "username": "user17",
      "id": 17
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Barker Mann",
      "email": "barkermann@quintity.com",
      "username": "user18",
      "id": 18
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Spencer Snider",
      "email": "spencersnider@quintity.com",
      "username": "user19",
      "id": 19
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Solis Garrison",
      "email": "solisgarrison@quintity.com",
      "username": "user20",
      "id": 20
    },
    "role": "Contributor"
  }
],
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
			this.$api
				.get(`sites/${this.$route.params.site_id}?include=users`)
				.then(({ data: json }) => {
					this.siteTitle = json.data.name;
					this.users = json.data.users || [];
					this.loading = false;
				});
		},

		createFilter(searchKeys, searchTerm) {
			return user => searchKeys.some(
				key => (user.user[key].toLowerCase().indexOf(searchTerm) !== -1)
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