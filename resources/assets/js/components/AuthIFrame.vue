<template>
	<div v-show="!hasAPIToken" id="authiframe">
		<iframe v-show="loginFormShown" :src="testURL"></iframe>
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
		background: rgba(0,0,0,0.5);
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
import {mapState, mapMutations, mapGetters} from 'vuex';
export default {

	name: 'AuthIFrame',

	data() {
		return {
			tickStart: null,
			latestTick: null,
			ticking: false
		}
	},

	created() {
		window.addEventListener("message", this.receiveMessage, true);
		this.initiateTick();
	},

	computed: {
		...mapState({
			apiToken: state => state.user.apiToken
		}),

		testURL() {
			return Config.get('auth_url');
		},

		...mapGetters('auth', [
			'hasAPIToken'
		]),

		loginFormShown() {
			const age = this.latestTick - this.tickStart; // just so it is reactive to the tick
			let iframe = this.getIframe();
			if(iframe) {
				let usernameInput = iframe.querySelector('input[name="username"]');
				if(usernameInput){
					return true;
				}
			}
			return false;
		}
	},

	watch: {
		loginFormShown(newValue) {
			console.log('loginFormShown changed to', newValue);
			if (newValue) {
				this.resetTick();
			}
		},

		hasAPIToken(newValue) {
			console.log('hasAPIToken changed to', newValue);
			if (!newValue) {
				this.reloadIframe();
				this.initiateTick();
			}
		}
	},

	methods: {
		...mapMutations('auth', [
			'setAPIToken'
		]),

		receiveMessage(e) {
			if(e.data.jwt !== void 0) {
				this.setAPIToken(e.data.jwt);
				this.resetTick();
			}
		},

		initiateTick() {
			this.tickStart = Date.now();
			this.ticking = true;
			this.tick();
		},

		resetTick() {
			this.ticking = false;
			this.tickStart = null;
			this.latestTick = null;
		},

		tick() {
			setTimeout(() => {
				if (this.ticking) {
					console.log('tick');
					this.latestTick = Date.now();
					this.tick();
				}
				else {
					this.resetTick();
				}
			}, 500);
		},

		getIframe() {
			let iframe = document.querySelector('#authiframe iframe');

			if(iframe){
				iframe = (iframe.contentWindow || iframe.contentDocument);
				if (iframe.document) {
					return iframe.document;
				}
			}

			return null;
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