<template>
	<div style="position:relative">
		<AuthIFrame ></AuthIFrame>
		<div :class="{ editor: addStyles }" :style="wrapperStyles" :v-if="permissions.globalRole && permissions">
			<router-view name="topbar" />
			<router-view />
		</div>
	</div>
</template>

<script>
import { mapState , mapActions} from 'vuex';
import Config from 'classes/Config';
import AuthIFrame from 'components/AuthIFrame';

export default {
	name: 'App',
	components:{
		AuthIFrame
	},
	data() {
		return {
			addStyles: true
		}
	},

	created() {
		// moved this logic to when we receive the jwt in the authiframe
	},

	computed: {
		...mapState([
			'wrapperStyles',
			'permissions'
		])
	}
};
</script>