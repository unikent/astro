<template>
<el-card>
	<div slot="header" class="card__header">
		<span class="card__header-text">Your pages</span>
	</div>
	<ul class="menu-page-list">
		<li
			v-for="(page, index) in sitePages"
			class="menu-page-list__page"
		>
			<div style="display: flex; flex-direction: column;">
				<span>{{ page.title }}</span>
				<span style="font-size: 11px; color: #71787f; margin-top: 4px;">{{ page.path }}</span>
			</div>

			<el-button @click="onClick({ text: page.title, url: page.path})" size="small" style="margin-left: auto;">
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
