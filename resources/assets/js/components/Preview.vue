<template>
<div id="main_content">
	<div id="b-wrapper" ref="container">
		<page-block
			v-for="(blockData, index) in page.blocks"
			:key="`block-${blockData.id}`"
			:blockData="blockData"
			:scale="scale"
			:sizes="sizes"
			:index="index">
		</page-block>
	</div>
</div>
</template>

<script>
	import Vue from 'vue';
	import PageBlock from './PageBlock.vue';
	import page from '../stubs/page';
	import eventBus from '../libs/event-bus.js';
	import Editor from './Editor';

	export default {
		name: 'wrapper',

		components: {
			PageBlock
		},

		data() {
			return {
				page,
				scale: 1,
				sizes: [],
				all: []
			};
		},

		created() {
			eventBus.$on('block-size', block => {
				this.sizes[block.idx] = block.height;

				// all blocks have loaded
				if(this.sizes.length === page.blocks.length) {
					console.log('done');
				}
			});
		},

		mounted() {
			new Editor();
		}
	}
</script>