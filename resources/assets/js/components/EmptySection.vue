<template>
<div v-else class="empty-section">
	<el-button size="large" @click="addBlocks">Add block(s) to this section</el-button>
</div>
</template>

<script>
import { mapMutations } from 'vuex';

export default {

	name: 'empty-section',

	methods: {

		...mapMutations([
			'showBlockPicker',
			'updateInsertIndex',
			'updateInsertRegion'
		]),

		addBlocks() {
			this.updateInsertIndex(0);
			this.updateInsertRegion(this.name);
			this.showBlockPicker();
		}
	}

};
</script>