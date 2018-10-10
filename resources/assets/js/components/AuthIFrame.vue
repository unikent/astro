<template>
	<div v-show="!hasAPIToken" id="authiframe">
		<iframe v-show="showIFrame" :src="testURL"></iframe>
	</div>	
</template>
<style scoped>
	#authiframe {
		position: absolute;
		z-index: 99999;
		top: 0px;
		left: 0px;
		right: 0px;
		bottom: 0px;
		background: transparent;
	}
	#authiframe iframe {
		width: 100%; 
		height: 100%;
		border: none;
		background: white;
	}

</style>
<script>
import 'store'
import Config from 'classes/Config.js';
import {mapState, mapMutations, mapGetters, mapActions} from 'vuex';
export default {

	name: 'AuthIFrame',

	data() {
		return {
			timer: null,
			showIFrame: false,
		}
	},

	created() {
		window.addEventListener("message", this.receiveMessage, true);
		// because watched property won't be called when this component is created
		if(!this.hasAPIToken) {
			this.reloadIframe();
			this.initiateTick();
		}
	},

	destroyed() {
		window.removeEventListener("message", this.receiveMessage);
	},
	computed: {
		...mapState({
			apiToken: state => state.user.apiToken
		}),

		testURL() {
			return Config.get('auth_url');
		},

		...mapGetters('auth', [
			'hasAPIToken',
			'username'
		]),
	},

	watch: {
		hasAPIToken(newValue) {
			console.log('hasAPIToken changed to', newValue);
			if (!newValue) {
				this.reloadIframe();
				this.initiateTick();
			}
			else {
				this.resetTick();
			}
		}
	},

	methods: {
		...mapMutations('auth', [
			'setAPIToken'
		]),

		
		...mapActions([
			'loadPermissions',
			'loadGlobalRole'
		]),
	

		// refresh the stored permissions and global role
		refreshGlobalRoleAndPermissions() {
			this.loadGlobalRole(this.username);
			this.loadPermissions();
		},

		receiveMessage(e) {
			if(e.data.jwt !== void 0) {
				this.resetTick();
				let lastUsername = this.username;
				this.setAPIToken(e.data.jwt);
				let newUsername = this.username;
				// refrest token is new or different user
				if (lastUsername !== newUsername) {
					this.refreshGlobalRoleAndPermissions();
				}
			}
		},

		initiateTick() {
			this.resetTick();
			this.timer = setTimeout(() => {
				this.showIFrame = true;
				this.timer = null;
			},1000);
		},

		resetTick() {
			if(this.timer) {
				clearTimeout(this.timer);
			}
			this.showIFrame = false;
			this.timer = false;
		},

		reloadIframe() {
			let iframe = document.querySelector('#authiframe iframe');
			if (iframe) {
				iframe.src += '';
			}
		}

	},
};
</script>