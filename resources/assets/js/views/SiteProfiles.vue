<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Manage Profiles</span>
		<el-button
			v-if="canUser('profile.create')"
			type="primary"
			class="manage-table__add-button"
			@click="showCreateProfileModal"
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
		:default-sort="{ prop: 'second_name', order: 'ascending' }"
		@sort-change="handleSortChange"
		border
	>
		<el-table-column
			prop="second_name"
			label="Name"
			sortable="custom"
		>
			<template slot-scope="scope">
				<div>
					<el-tooltip
						v-if="statuses[scope.row.status]"
						:content="statuses[scope.row.status].name"
					>
						<div
							class="site-profiles__status"
							:class="{[`site-profiles__status--${scope.row.status}`]: true }"
							style="display: inline-block;"
						/>
					</el-tooltip>
					{{ scope.row.title }} {{ scope.row.first_name }} {{ scope.row.second_name }}
				</div>
				<div class="site-profiles__published-date">
					Last published {{ scope.row.created_at }} by ?
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
				{{ scope.row.categories.map(cat => cat.name).join(', ') || '–' }}
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
							:disabled="scope.row.status === 'published'"
						>
							Publish
						</el-dropdown-item>

						<el-dropdown-item
							command="unpublish"
							v-if="canUser('profile.unpublish')"
							:disabled="scope.row.status === 'new'"
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
		this.statuses = {
			'new': {
				name: 'Unpublished',
				type: 'primary'
			},
			'draft': {
				name: 'Draft',
				type: 'warning'
			},
			'published': {
				name: 'Published',
				type: 'success'
			}
		};

		this.fetchProfiles();
	},

	data() {
		return {
			filters: ['first_name', 'second_name', 'job_titles', 'email', 'categories.0.name'],
			profiles: [],
			displayProfileCreationModal: false
		};
	},

	computed: {
		...mapGetters([
			'canUser'
		]),

		items() {
			return this.profiles.map(profile => (profile['status'] = 'published') && profile.draft[0]);
		},
	},

	methods: {
		fetchProfiles() {
			this.$api
				.get('sites/1/profiles?include=draft,categories,socialmedia', {})
				.then(({ data: json }) => {
					this.profiles = json.data;
				});
		},

		handleCommand() {},

		categorySort(a, b) {
			if(!a.categories[0]) {
				return 1;
			}
			if(!b.categories[0]) {
				return -1;
			}

			a = a.categories[0].name;
			b = b.categories[0].name;

			return a === b ? 0 : (a < b ? -1 : 1);
		},

		showCreateProfileModal() {
			this.$bus.$emit('site-profile:showCreateProfileModal');
		}
	}

};
</script>
