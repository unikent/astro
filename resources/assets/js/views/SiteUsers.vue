<template>
<div class="site-users">
	<el-card>
		<div slot="header" class="card__header">
			<span class="card__header-text">
				{{ this.siteTitle }} site users
			</span>
		</div>

		<h3>Add new users to {{ this.siteTitle }}</h3>
		<div style="display: flex; padding: 10px; margin-bottom: 40px; background-color: #f7f9fb;">

			<el-select
				v-model="usersToAdd"
				multiple
				filterable
				placeholder="Search for a user" style="width: 70%">
				<el-option
				v-for="user in userList"
				:key="user.user.id"
				:label="`${user.user.name} @ ${user.user.username}`"
				:value="user.user.id">
				</el-option>
			</el-select>

			<el-select v-model="selectedRole" placeholder="Select role" class="u-flex-auto-left">
				<el-option
					label="Site Owner"
					value="Site Owner"
				/>
				<el-option
					label="Editor"
					value="Editor"
				/>
				<el-option
					label="Contributor"
					value="Contributor"
				/>
			</el-select>

			<el-button @click="addUser" type="primary" class="u-flex-auto-left">Add user{{ multipleUsersToAdd ? 's' : ''}}</el-button>
			
		</div>


		<h3>Existing users</h3>
		<div style="display: flex; margin-bottom: 20px;">
			<el-input
				v-model="searchInput"
				placeholder="Find a user"
				icon="search"
				@change="search"
				:on-icon-click="search"
				style="width: 200px;"
			/>
			<el-select v-model="roleFilter" placeholder="Role" class="u-flex-auto-left" style="width: 130px;">
				<el-option
					label="No filter"
					:value="null"
				/>
				<el-option-group label="Filter by role">
					<el-option
						label="Site Owner"
						value="Site Owner"
					/>
					<el-option
						label="Editor"
						value="Editor"
					/>
					<el-option
						label="Contributor"
						value="Contributor"
					/>
				</el-option-group>
			</el-select>
		</div>

		<div v-if="pagedUsers.length" class="el-table w100 el-table--fit el-table--striped el-table--border el-table--enable-row-hover">
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
								<el-select v-model="user.role" size="small" class="u-flex-auto-left">
									<el-option-group label="Change role">
										<el-option
											label="Site Owner"
											value="Site Owner"
										/>
										<el-option
											label="Editor"
											value="Editor"
										/>
										<el-option
											label="Contributor"
											value="Contributor"
										/>
									</el-option-group>
								</el-select>
							</div>
						</td>
						<td>
							<div class="cell">
							<el-button @click="removeUser(user.id)" type="default" size="small">
								<icon name="delete" width="14" height="14" />
							</el-button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div v-else class="site-users__user--empty">
			No users
		</div>
	</el-card>

	<el-row>
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
	</el-row>
</div>
</template>

<script>
import Icon from 'components/Icon';

export default {

	name: 'site-users',

	props: {
		counts: {
			type: Array,
			default: () => [20, 50, 100, 200]
		}
	},

	components: {
		Icon
	},

	created() {
		this.fetchSiteData();
	},

	data() {
		return {
			siteTitle: '',
			roleFilter: null,
			searchInput: '',
			searchFilter: null,

			users: [],
			// serialised version of the users, to test equality
			// with current users for isUnsaved computed property
			initialUsers: null,

			currentPage: 1,
			count: 20,
			loading: true,

			userList: [],
			usersToAdd: [],
			selectedRole: ''
		};
	},

	computed: {

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
					this.initialUsers = JSON.stringify(this.users);

					this.loading = false;
				});
		},

		search() {
			if(this.searchInput.length > 2) {
				this.searchFilter = this.searchInput;
			}
			else if(this.searchFilter !== null) {
				this.searchFilter = null;
			}
		},

		createFilter(searchKeys, searchTerm) {
			return user => searchKeys.some(
				key => (user.user[key].indexOf(searchTerm) !== -1)
			);
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
		},

		handleCountChange(newSize) {
			this.count = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		}
	}
};
</script>