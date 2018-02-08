<template>
<div v-loading.fullscreen.lock="loading">
	<div v-for="(value, key) in profileData"
		style="border-radius: 4px; background-color: #fff; padding: 30px; margin: 20px;"
	>
		<h3>{{ key.replace(/_/g, ' ') }}</h3>
		<p>{{ value }}</p>
	</div>
</div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
	name: 'profile-preview',

	props: ['site-id', 'profile-id'],

	created() {
		this.loadProfileData();
	},

	computed: {
		...mapState({
			loading: state => state.profile.loading,
			profileData: state => state.profile.profileData
		})
	},

	methods: {
		...mapActions({
			fetchProfileData: 'profile/fetchProfileData'
		}),

		loadProfileData() {
			this.fetchProfileData({
				siteId: this.siteId,
				profileId: this.profileId
			})
		}
	}

};
</script>
