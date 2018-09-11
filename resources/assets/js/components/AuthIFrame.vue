<template>
	<div id="authiframe">
		<iframe v-if="!hasAPIToken" :src="testURL"></iframe>
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
import {mapState, mapMutations, mapGetters} from 'vuex';
export default {

	name: 'AuthIFrame',
	created() {
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
		},
		...mapGetters('auth', [
			'hasAPIToken'
		])
	
	},

	data() {
		return {
			waiting: null,
			waitFor: 3000,
		}
	},
	methods: {
		...mapMutations('auth', [
			'setAPIToken'
		]),

		receiveMessage(e) {
			if(e.data.jwt !== void 0) {
				console.log(e);
				clearTimeout(this.waiting);
				this.waiting = null;
				this.setAPIToken(e.data.jwt);
			}
		}
	},
};
</script>