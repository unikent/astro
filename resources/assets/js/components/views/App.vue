<template>
	<div :class="{ editor: isEditor }">
		<router-view></router-view>
		<div v-if="isEditor" class="top-bar">
			<div v-show="showBack" @click="backToSites" class="top-bar-backbutton">
				<i class="el-icon-arrow-left backbutton-icon"></i>Back to sites
			</div>
			<!-- <div id="toolbar"></div> -->
			<el-dropdown trigger="click" @command="handleCommand" class="user-menu-button">
				<span class="el-dropdown-link">
					{{ username }}<i class="el-icon-caret-bottom el-icon--right"></i>
				</span>
				<el-dropdown-menu slot="dropdown">
					<el-dropdown-item command="sign-out">Sign out</el-dropdown-item>
				</el-dropdown-menu>
			</el-dropdown>
			<div class="top-title">{{ pageName }}</div>
		</div>
	</div>
</template>

<script>
import { mapState } from 'vuex';
import SnackBar from '../SnackBar.vue';

/* global window */

export default {
	name: 'App',

	components: {
		SnackBar
	},

	computed: {
		isEditor() {
			return window.isEditor;
		},

		username() {
			return window.Laravel.username;
		},

		// TODO: clean up hack
		showBack() {
			return !!this.$route.path.match(/\/site\/[^\/]+(\/page\/[^\/]+)?/)
		},

		...mapState([
			'pageName'
		])
	},

	methods: {
		handleCommand(command) {
			if(command === 'sign-out') {
				window.location = '/auth/logout';
			}
		},

		backToSites() {
			this.$store.dispatch('changePage', '');
			this.$router.push('/sites')
		}
	}
};
</script>