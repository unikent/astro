<template>
<el-card>
	<div slot="header" class="card__header">
		<span class="card__header-text">Site pages</span>
	</div>
	<ul class="site-page-links">
		<li
			v-for="(page, index) in sitePages"
			class="site-page-links__item"
			:id="'site-page-link-' + index"
		>
			<div class="site-page-links__site-details">
				<span>{{ page.title }}</span>
				<span class="site-page-links__add-button">{{ page.path }}</span>
			</div>

			<el-button @click="onClick({ text: page.title, url: page.path})" size="small" class="u-flex-auto-left add-to-menu-button">
				Add to menu
			</el-button>
		</li>
	</ul>
</el-card>
</template>

<script>
export default {
	name: 'site-page-links',
	props: ['onClick', 'sitePages']
};
</script>
