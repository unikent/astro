<template>
	<div class="not-found">
		<h1 class="not-found__title">Something went wrong...</h1>
		<div class="not-found__text">
			<p>We'd like to know what happened so we can fix it.</p>
			<p>Let the support team know what page this happened on, and what you were trying to do at the time.</p>
			<p>Thanks!</p>
		</div>
		<a class="el-button el-button--primary not-found__link" :href="baseUrl">Back home</a>
	</div>
</template>
<script>
	import Config from 'classes/Config';

	export default {
		computed: {
			baseUrl() {
				return Config.get('base_url') + '/';
			}
		}
	}
</script>