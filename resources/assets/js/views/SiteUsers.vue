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

			<el-button @click="addUser" type="primary" class="u-flex-auto-left">Add a new user</el-button>
			
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
								Role
							</div>
						</th>
						<th>
							<div class="cell">
								Email
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
								{{ user.user.email }}
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

			userList: [
				{
    "user": {
      "name": "Howard Reilly",
      "email": "howardreilly@quintity.com",
      "username": "user1",
      "id": 1
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Pugh Bradshaw",
      "email": "pughbradshaw@quintity.com",
      "username": "user2",
      "id": 2
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Lillian Allen",
      "email": "lillianallen@quintity.com",
      "username": "user3",
      "id": 3
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Puckett Jefferson",
      "email": "puckettjefferson@quintity.com",
      "username": "user4",
      "id": 4
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "French Foreman",
      "email": "frenchforeman@quintity.com",
      "username": "user5",
      "id": 5
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Valerie Arnold",
      "email": "valeriearnold@quintity.com",
      "username": "user6",
      "id": 6
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Gabrielle Henderson",
      "email": "gabriellehenderson@quintity.com",
      "username": "user7",
      "id": 7
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Nielsen Morse",
      "email": "nielsenmorse@quintity.com",
      "username": "user8",
      "id": 8
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Adeline Craig",
      "email": "adelinecraig@quintity.com",
      "username": "user9",
      "id": 9
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Ethel Lawson",
      "email": "ethellawson@quintity.com",
      "username": "user10",
      "id": 10
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Janna Horn",
      "email": "jannahorn@quintity.com",
      "username": "user11",
      "id": 11
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Yvonne Pickett",
      "email": "yvonnepickett@quintity.com",
      "username": "user12",
      "id": 12
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Hoffman Murray",
      "email": "hoffmanmurray@quintity.com",
      "username": "user13",
      "id": 13
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Karen Meyer",
      "email": "karenmeyer@quintity.com",
      "username": "user14",
      "id": 14
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Conrad Bates",
      "email": "conradbates@quintity.com",
      "username": "user15",
      "id": 15
    },
    "role": "Editor"
  },
  {
    "user": {
      "name": "Kemp Levine",
      "email": "kemplevine@quintity.com",
      "username": "user16",
      "id": 16
    },
    "role": "Contributor"
  },
  {
    "user": {
      "name": "Esther Richards",
      "email": "estherrichards@quintity.com",
      "username": "user17",
      "id": 17
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Ratliff Henry",
      "email": "ratliffhenry@quintity.com",
      "username": "user18",
      "id": 18
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Ramsey Huber",
      "email": "ramseyhuber@quintity.com",
      "username": "user19",
      "id": 19
    },
    "role": "Site Owner"
  },
  {
    "user": {
      "name": "Guzman Britt",
      "email": "guzmanbritt@quintity.com",
      "username": "user20",
      "id": 20
    },
    "role": "Site Owner"
  }
			],
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
				key => (user.user[key].indexOf(searchTerm) > -1)
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