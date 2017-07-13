<template>
<div v-else class="empty-region">
	<el-button size="large" @click="addBlocks">Add block(s) to this region</el-button>
</div>
</template>

<script>
import { mapMutations } from 'vuex';

export default {

	name: 'empty-region',

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