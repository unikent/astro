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
</el-card>
</template>

<script>
import { mapGetters } from 'vuex';

export default {

	name: 'site-profiles',

	computed: {
		...mapGetters([
			'canUser'
		])
	}

};
</script>
