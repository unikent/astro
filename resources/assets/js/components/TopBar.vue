<template>
	<div class="top-bar" v-if="showBack">
		<div v-show="showBack" @click="backToSites" class="top-bar-backbutton">
			<i class="el-icon-arrow-left backbutton-icon"></i>Site list
		</div>

		<div class="top-bar__tools">

			<toolbar/>

			<el-dropdown trigger="click" @command="handleCommand" class="user-menu-button">
				<span class="el-dropdown-link">
					{{ username }}<i class="el-icon-caret-bottom el-icon--right"></i>
				</span>
				<el-dropdown-menu slot="dropdown">
					<el-dropdown-item command="sign-out">Sign out</el-dropdown-item>
				</el-dropdown-menu>
			</el-dropdown>
		</div>
	</div>
	<div v-else class="top-bar top-bar--homepage">
		<el-dropdown trigger="click" @command="handleCommand" class="user-menu-button">
			<span class="el-dropdown-link">
				{{ username }}<i class="el-icon-caret-bottom el-icon--right"></i>
			</span>
			<el-dropdown-menu slot="dropdown">
				<el-dropdown-item command="sign-out">Sign out</el-dropdown-item>
			</el-dropdown-menu>
		</el-dropdown>
	</div>
</template>

<script>
import { mapState } from 'vuex';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import Toolbar from 'components/sidebar/Toolbar';

/* global window, document */

export default {

	name: 'top-bar',

	components: {
		Icon,
		Toolbar
	},

	created() {
		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		document.addEventListener('keydown', this.onKeyDown);
		document.addEventListener('keyup', this.onKeyUp);
	},

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
	},

	computed: {

		...mapState({
			pageName: state => state.page.pageName,
		}),

		showBack() {
			return ['site', 'page'].indexOf(this.$route.name) !== -1;
		},

		username() {
			return window.astro.username;
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
			this.$store.commit('setPage', {});
			this.$store.commit('setLoaded', false);
			undoStackInstance.clear();
			this.$router.push('/sites');
		}
	}
};
</script>
