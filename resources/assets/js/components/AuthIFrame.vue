<template>
	<div id="authiframe">
		<iframe v-if="showIFrame" :src="testURL"></iframe>
	</div>
</template>
<style scoped>
	#authiframe {
		background-color: transparent;
		display: none;
		position: absolute;
		z-index: 99999;
		top: 0px;
		left: 0px;
		right: 0px;
		bottom: 0px;
	}
	iframe {
		background:rgba(0,0,0,0.5); width: 100%; height: 100%;
	}
</style>
<script>
import 'store'
import Config from 'classes/Config.js';
import {mapState, mapMutations} from 'vuex';
export default {

	name: 'AuthIFrame',
	created() {
		this.showIFrame = true;
		this.waiting = setTimeout(function() {
			document.getElementById('authiframe').style.display = 'block';
		}, this.waitFor);
		window.addEventListener("message",this.receiveMessage, true);
	},
	computed: {
		...mapState({
			apiToken: state => state.user.apiToken
		}),
		testURL() {
			return Config.get('auth_url');
		}
	},
	data() {
		return {
			waiting: null,
			waitFor: 3000,
			showIFrame: false,
		}
	},
	methods: {
		// @TODO find the way to specify the namespace here to make things look nice
		// ...mapMutations([
		// 	'user/setAPIToken',
		// ]),
		receiveMessage(e) {
			if(e.data.jwt !== void 0) {
				console.log(e);
				clearTimeout(this.waiting);
				this.waiting = null;
				this.showIFrame = false;
				this.$store.commit('auth/setAPIToken', e.data.jwt);
				// this.setAPIToken(e.data.jwt);
			}
		}
	},
};
</script>