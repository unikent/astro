<template>
	<div class="top-bar" v-if="showBack">
		<div v-show="showBack" @click="backToSites" class="top-bar-backbutton">
			<i class="el-icon-arrow-left backbutton-icon"></i>Site list
		</div>

		<div class="top-bar__page-title">{{ pageTitle }} <el-tag type="primary">{{ pagePath }}</el-tag></div>

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
import { mapState, mapGetters, mapActions, mapMutations } from 'vuex';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import Toolbar from 'components/sidebar/Toolbar';
import promptToSave from '../mixins/promptToSave';
import Config from '../classes/Config.js';

/* global window, document */

export default {

	name: 'top-bar',

	components: {
		Icon,
		Toolbar
	},

	mixins:[ promptToSave ],

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
			pageTitle: state => state.page.pageTitle,
			pagePath: state => state.page.pagePath,
			pageSlug: state => state.page.pageSlug
		}),

		...mapGetters([
			'unsavedChangesExist'
		]),

		showBack() {
			return ['site', 'page'].indexOf(this.$route.name) !== -1;
		},

		username() {
			return window.astro.username;
		}
	},

	methods: {

		...mapActions([
			'handleSavePage'
		]),

		...mapMutations([
			'updateMenuActive'
		]),

		handleCommand(command) {
			if(command === 'sign-out') {
				this.promptToSave(() => {

                    var form = document.createElement("form");
                    form.setAttribute("method", 'post');
                    form.setAttribute("action", Config.get('base_url', '') + '/auth/logout');
                    var csrf = document.createElement("input");
                    csrf.setAttribute("type", "hidden");
                    csrf.setAttribute("name", "_token");
                    csrf.setAttribute("value", window.astro.csrf_token);
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                    form.submit();

//					window.location = Config.get('base_url', '') + '/auth/logout';
				});
			}
		},

		backToSites() {
			this.promptToSave(() => {
				this.$store.commit('changePage', {title: "Home page", path: '/', slug:'home'});
				this.$store.commit('setPage', {});
				this.$store.commit('setLoaded', false);
				undoStackInstance.clear();
				this.$router.push('/sites');
			})
		}
	}
};
</script>
