<template>
	<el-row class="el-row__pageitem">
		<el-col :sm="12" :md="8" :style="{'padding-left': (page.depth) + 'rem'}">
			<router-link :to="{name: 'page', params: {site_id: page.site_id, page_id: page.id}}">{{ page.title }}</router-link>
			<br><small style="padding-left: 0.5rem; color: #777;">{{ page.full_path }}</small>
		</el-col>
		<el-col :sm="8" :md="8">
			Edited: {{ page.revision.updated_at }}
		</el-col>
	</el-row>
</template>

<script>
	export default {
		name: 'page-item',
		props: {
			page: {
				type: Object,
				required: true,
			},
			disabled: {
				type: Boolean,
				default: false,
			}
		},
	}
</script>