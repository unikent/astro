<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Manage Profiles</span>
		<el-button
			v-if="canUser('profile.create')"
			type="primary"
			class="manage-table__add-button"
		>
			Add Profile
		</el-button>
	</div>

	<div class="filter-site-profiles">
		<el-input
			v-model="searchInput"
			placeholder="Find profiles"
			suffix-icon="el-icon-search"
			class="filter-site-profiles__searchbox"
		/>
	</div>

	<el-table
		:data="pagedItems"
		:default-sort="{ prop: 'name', order: 'ascending' }"
		@sort-change="handleSortChange"
		border
	>
		<el-table-column
			prop="name"
			label="Name"
			sortable="custom"
		>
			<template slot-scope="scope">
				<div>{{ scope.row.name }}</div>
				<div class="site-profiles__published-date">
					Last published {{ scope.row.test_date }} by {{ scope.row.job_title }}
				</div>
			</template>
		</el-table-column>
		<el-table-column
			prop="categories"
			label="Categories"
			sortable="custom"
			:sort-method="categorySort"
		>
			<template slot-scope="scope">
				{{ scope.row.categories.join(', ') }}
			</template>
		</el-table-column>
		<el-table-column
			label="Action"
			width="80"
			align="center"
		>
			<template slot-scope="scope">
				<el-dropdown trigger="click" @command="handleCommand">
					<el-button type="text" size="small">
						<icon name="more-alt" width="14" height="14" style="fill: #677b98" />
					</el-button>

					<el-dropdown-menu slot="dropdown">
						<el-dropdown-item
							command="publish"
							v-if="canUser('profile.publish')"
						>
							Publish
						</el-dropdown-item>

						<el-dropdown-item
							command="unpublish"
							v-if="canUser('profile.unpublish')"
						>
							Unpublish
						</el-dropdown-item>

						<el-dropdown-item
							command="remove"
							divided
							v-if="canUser('profile.delete')"
						>
							Delete
						</el-dropdown-item>
					</el-dropdown-menu>
				</el-dropdown>
			</template>
		</el-table-column>
	</el-table>

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

	<create-site-profile-modal />

</el-card>
</template>

<script>
import { mapGetters } from 'vuex';

import filterableMixin from 'mixins/filterableMixin';
import paginatableMixin from 'mixins/paginatableMixin';
import Icon from 'components/Icon';
import CreateSiteProfileModal from 'components/CreateSiteProfileModal';

export default {

	name: 'site-profiles',

	mixins: [filterableMixin, paginatableMixin],

	components: {
		Icon,
		CreateSiteProfileModal
	},

	created() {
		this.fetchProfiles();
	},

	data() {
		return {
			filters: ['name', 'job_title', 'email', 'categories.0'],
			profiles: []
		};
	},

	computed: {
		...mapGetters([
			'canUser'
		]),

		items() {
			return this.profiles;
		}
	},

	methods: {
		fetchProfiles() {
			// TODO: Fetch data from API here
			// this.profiles = [];
		},

		handleCommand() {},

		categorySort(a, b) {
			if(!a.categories[0]) {
				return 1;
			}
			if(!b.categories[0]) {
				return -1;
			}

			a = a.categories[0];
			b = b.categories[0];

			return a === b ? 0 : (a < b ? -1 : 1);
		}
	}

};
</script>
