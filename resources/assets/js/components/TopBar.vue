<template>
	<div class="top-bar">
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
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { Loading } from 'element-ui';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import Toolbar from 'components/Sidebar/Toolbar';

/* global window */

export default {

	name: 'top-bar',

	components: {
		Icon,
		Toolbar
	},

	data() {
		return {
			form: {
				message: ''
			}
		}
	},

	created() {
		this.views = {
			desktop: {
				icon: 'desktop',
				label: 'Desktop',
				width: '100%',
				height: '100vh'
			},
			tablet: {
				icon: 'tablet',
				label: 'Tablet',
				width: '768px',
				height: '1024px'
			},
			mobile: {
				icon: 'mobile',
				label: 'Mobile',
				width: '320px',
				height: '568px'
			}
		};

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		document.addEventListener('keydown', this.onKeyDown);
		document.addEventListener('keyup', this.onKeyUp);
	},

	computed: {

		...mapState([
			'currentView',
			'publishModal'
		]),

		...mapState({
			pageName: state => state.page.pageName,
		}),

		showBack() {
			return ['site', 'page'].indexOf(this.$route.name) !== -1;
		},

		username() {
			return window.astro.username;
		},

		dimensions() {
			return {
				width: this.views[this.currentView].width,
				height: this.views[this.currentView].height
			};
		},

		publish_modal: {
			get() {
				return this.publishModal.visible;
			},
			set(value) {
				if(value) {
					this.showPublishModal();
				}
				else {
					this.hidePublishModal();
				}
			}
		}
	},

	methods: {

		...mapMutations([
			'changeView',
			'showPublishModal',
			'hidePublishModal'
		]),

		publishPage() {
			this.$api
				.post(`page/${this.$route.params.site_id}/publish`, this.page)
				.then(() => {
					this.hidePublishModal();
					this.$message({
						message: 'Published page',
						type: 'success',
						duration: 2000
					});
					this.form.message = '';
				})
				.catch(() => {});
		},

		cancelPublish() {
			this.hidePublishModal();
			this.form.message = '';
		},

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
