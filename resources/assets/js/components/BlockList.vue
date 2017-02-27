<template>
<div>
	<h2 class="block-list-header">Available blocks</h2>
	<ul class="block-list">
		<!-- <draggable @start="drag=true" @end="drag=false"> -->
		<li v-for="block in blocks" v-if="block">
			<div class="block-move">{{ block.name }}</div>
		</li>
		<!-- </draggable> -->
	</ul>
</div>
</template>

<script>
	import api from '../libs/api';
	import draggable from 'vuedraggable';

	export default {

		components : {
			draggable
		},

		computed: {
			blocks() {
				return this.$store.state.blockList;
			}
		},

		methods: {
			fetchData() {
				this.$store.dispatch('fetchBlockList');
			}
		},

		mounted() {
			this.fetchData();
		}
	}
</script>

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
		transition: background-color .2s ease-out;
		cursor: move;
		user-select: none;
	}

	.block-move:hover {
		background-color: #f5f5f5;
	}
}

.block-list-header {
	text-align: center;
	margin: 10px 20px;
	font-weight: normal;
	text-transform: capitalize;
}
</style>