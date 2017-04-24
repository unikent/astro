<style>
.top-bar {
	height: 50px;
	border-bottom: 1px solid #dce3ea;
	background-color: white;
	box-shadow: 0 0px 10px rgba(0, 0, 0, 0.05);
	width: 100%;
	position: absolute;
	top: 0;
	left: 0;
	padding: 7px 20px 7px 7px;
}
.top-bar-backbutton {
	float: left;
	line-height: 35px;
	color: #666;
	font-size: 13px;
	cursor: pointer
}
.backbutton-icon {
	margin-right: 10px;
}
.user-menu-button {
	float: right;
}

.top-bar .el-dropdown-link {
	cursor: pointer;
	line-height: 35px;
	display: inline-block;
}

.top-bar .el-icon-caret-bottom {
	margin-left: 10px;
	font-size: 10px;
}

.top-title {
	text-align: center;
	line-height: 35px;
}
</style>

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