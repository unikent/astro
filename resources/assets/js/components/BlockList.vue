<style lang="scss">
.block-list {
	list-style: none;
	padding: 0;
	margin: 0;

	li {
		padding: 10px 20px;
	}

	.block-move {
		padding: 10px;
		background-color: #fff;
		border: 1px solid #d9dee7;
		border-radius: 3px;
		transition: background-color .2s ease-out, border .2s ease-out;
		cursor: move;
		user-select: none;
	}

	.block-move:hover {
		background-color: #eef1f6;
		border: 1px solid #bac3d4;
	}
}

.block-list-header {
	text-align: center;
	margin: 10px 20px;
	font-weight: normal;
	text-transform: capitalize;
}
</style>

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
import BlockListItem from './BlockListItem.vue';
import { mapState } from 'vuex';

export default {

	components: {
		BlockListItem
	},

	computed: {
		...mapState([
			'over'
		]),
		blocks() {
			return this.$store.state.blockList;
		}
	},

	methods: {
		fetchData() {
			this.$store.dispatch('fetchBlockList');
		}
	},

	created() {
		this.fetchData();
	}
};
</script>
