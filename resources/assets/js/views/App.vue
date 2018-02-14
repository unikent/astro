<template>
	<div :class="{ editor: addStyles }" :style="wrapperStyles" :v-if="globalRole && permissions">
		<router-view name="topbar" />
		<router-view />
	</div>
</template>

<script>
import { mapState , mapActions} from 'vuex';
import Config from 'classes/Config';

export default {
	name: 'App',

	data() {
		return {
			addStyles: true
		}
	},

	created() {
		if( !this.globalRole) {
			this.loadPermissions();
			this.loadGlobalRole(Config.get('username'));
		}
	},

	computed: {
		...mapState([
			'wrapperStyles',
			'globalRole',
			'permissions'
		])
	},

	methods: {
		...mapActions([
			'loadPermissions',
			'loadGlobalRole'
		]),
	}
};
</script>