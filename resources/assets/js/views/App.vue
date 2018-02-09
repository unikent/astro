<template>
	<div :class="{ editor: addStyles }" :style="wrapperStyles">
		<router-view name="topbar" />
		<router-view />
	</div>
</template>

<script>
import { mapState } from 'vuex';

export default {
	name: 'App',

	data() {
		return {
			addStyles: true
		}
	},

	computed: {
		...mapState([
			'wrapperStyles'
		])
	}
};
</script>