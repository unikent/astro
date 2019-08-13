<template>
	<li v-if="matchesFilter">
		<router-link :to="{name: 'page', params: {site_id: page.site_id, page_id: page.id}}">{{ page.title }}</router-link>
		<span>edited {{ page.revision.updated_at }}</span>
		<ul v-if="page.children && page.children.length > 0">
			<page-item
					v-for="child in page.children"
					:page="child"
					:key="child.id"
					:filter="filter"
					:statusFilter="statusFilter"
			></page-item>
		</ul>
	</li>
</template>

<script>
	export default {
		name: 'page-item',
		props: {
			page: {
				type: Object,
				required: true,
			},
			filter: {
				type: String,
				default: '',
			},
			statusFilter: {
				type: String,
				default: '',
			},
		},
		computed: {
			matchesFilter() {
				return this.shouldPageBeDisplayed(this.page) || this.filterChildren(this.page.children || []).length > 0;
			}
		},
		methods: {
			filterChildren(pages) {
				if(!pages) {
					return [];
				}
				return pages.filter((page) => {
					if(this.shouldPageBeDisplayed(page) || this.filterChildren(page.children).length > 0) {
						return true;
					}
				});
			},
			shouldPageBeDisplayed(page) {
				const passesFilter = (!this.filter ||
					(page.title && page.title.toLowerCase().indexOf(this.filter) !== -1));
				const passesStatusFilter = (!this.statusFilter || this.statusFilter === page.status);
				return passesFilter && passesStatusFilter;
			}
		}
	}
</script>