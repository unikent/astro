<template>
<div>
	<div v-for="(value, key) in profileData"
		style="border-radius: 4px; background-color: #fff; padding: 30px; margin: 20px;"
	>
		<h3>{{ key.replace(/_/g, ' ') }}</h3>
		<p>{{ value }}</p>
	</div>
</div>
</template>

<script>
export default {
	name: 'profile-preview',

	props: ['site-id', 'profile-id'],

	data() {
		return {
			profileData: {}
		};
	}

};
</script>
