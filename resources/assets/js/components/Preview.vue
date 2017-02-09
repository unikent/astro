<template>
<div id="main_content">
	<div id="b-wrapper" ref="container">
		<page-block
			v-for="(blockData, index) in page.blocks"
			:key="`block-${blockData.id}`"
			:blockData="blockData"
			:scale="scale"
			:index="index">
		</page-block>
	</div>
</div>
</template>

<script>
	import Vue from 'vue';
	import PageBlock from './PageBlock.vue';
	import page from '../stubs/page';
	import Editor from './Editor';

	window.emitter = new Vue();

	export default {
		name: 'wrapper',

		components: {
			PageBlock
		},

		data() {
			return {
				page,
				scale: 1
			};
		},

		mounted() {
			new Editor();
		}
	}
</script>