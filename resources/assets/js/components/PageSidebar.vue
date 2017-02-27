<template>
	<el-tabs v-model="activeName" @tab-click="handleClick">
		<el-tab-pane label="Site" name="first">
			<page-list></page-list>
		</el-tab-pane>
		<el-tab-pane label="Page" name="second">
			Page options here
		</el-tab-pane>
	</el-tabs>
</template>
<script>
	import PageList from './PageList.vue';

	export default {

		components: {
			PageList
		},

		data() {
			return {
				activeName: 'first'
			};
		},

		methods: {
			handleClick(tab, event) {
				console.log(tab, event);
			}
		}
	};
</script>