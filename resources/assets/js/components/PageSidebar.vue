<template>
	<el-tabs v-model="activeName" @tab-click="handleClick" style="background-color: #f5f7fa; height: 100%;">
		<el-tab-pane label="Site" name="first">
			<page-list></page-list>
		</el-tab-pane>
		<el-tab-pane label="Page" name="second">
			<div class="side-container">Page options here</div>
		</el-tab-pane>
	</el-tabs>
</template>

<script>
import PageList from './PageList';

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