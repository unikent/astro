<template>
	<div :class="{ editor: isEditor }" :style="wrapperStyles">
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
import SnackBar from '../SnackBar';

/* global window */

export default {
	name: 'App',

	components: {
		SnackBar
	},

	computed: {
		...mapState([
			'wrapperStyles'
		]),

		...mapState({
			pageName: state => state.page.pageName,
		}),

		isEditor() {
			return window.isEditor;
		},

		username() {
			return window.astro.username;
		},

		// TODO: clean up hack
		showBack() {
			return !!this.$route.path.match(/\/site\/[^\/]+(\/page\/[^\/]+)?/)
		}
	},

	methods: {
		handleCommand(command) {
			if(command === 'sign-out') {
				window.location = '/auth/logout';
			}
		},

		backToSites() {
			this.$store.commit('changePage', '');
			this.$router.push('/sites')
		}
	}
};
</script>