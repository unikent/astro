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

	<div class="filter-user">
		<el-input
			v-model="searchInput"
			placeholder="Find profiles"
			suffix-icon="el-icon-search"
			class="filter-user__searchbox"
		/>
	</div>

	<el-table
		:data="profiles"
		:default-sort="{ prop: 'date', order: 'descending' }"
		border
	>
		<el-table-column
			prop="name"
			label="Name"
			sortable
		>
			<template slot-scope="scope">
				<div style="margin-bottom: 10px">{{ scope.row.name }}</div>
				<div style="color: #b8adb5" class="menu-editor__publihed-date">Last published {{ scope.row.test_date }} by {{ scope.row.job_title }}</div>
			</template>
		</el-table-column>
		<el-table-column
			prop="categories"
			label="Categories"
			sortable
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

</el-card>
</template>

<script>
import { mapGetters } from 'vuex';

import Icon from 'components/Icon';

export default {

	name: 'site-profiles',

	components: {
		Icon
	},

	data() {
		return {
			profiles: []
		};
	},

	computed: {
		...mapGetters([
			'canUser'
		])
	},

	methods: {
		handleCommand() {}
	}

};
</script>
