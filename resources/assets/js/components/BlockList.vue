<template>
<div>
	<h2 class="block-list-header">Available blocks</h2>
	<ul class="block-list">
		<li v-for="block in blocks" v-if="block">
			<block-list-item :block="block" />
		</li>
	</ul>
</div>
</template>

<script>
import BlockListItem from './BlockListItem';
import { mapState, mapActions } from 'vuex';

export default {

	components: {
		BlockListItem
	},

	computed: {
		...mapState([
			'over'
		]),
		blocks() {
			return this.$store.state.definition.blockList;
		}
	},

	methods: {
		...mapActions([
			'fetchBlockList'
		])
	},

	created() {
		this.fetchBlockList();
	}
};
</script>
