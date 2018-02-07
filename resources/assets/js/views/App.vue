<template>
	<div :class="{ editor: isEditor }" :style="wrapperStyles">
		<router-view></router-view>
		<top-bar v-if="isEditor" />
	</div>
</template>

<script>
import { mapState } from 'vuex';
import TopBar from 'components/TopBar';
import SnackBar from 'components/SnackBar';

export default {
	name: 'App',

	components: {
		TopBar,
		SnackBar
	},

	computed: {
		...mapState([
			'wrapperStyles'
		]),
		isEditor() {
			return ['preview', 'profile-preview', '404'].indexOf(this.$route.name) === -1;
		}
	}
};
</script>