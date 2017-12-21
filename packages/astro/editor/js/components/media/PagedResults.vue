<template>
<div>
	<results
		:results="pagedResults"
		:view="view"
		:columnCount="columnCount"
		:pickerMode="pickerMode"
		:allowMultiple="allowMultiple"
		:pickerAction="pickerAction"
	/>

	<el-row>
		<el-pagination
			@size-change="handleMediaCountChange"
			@current-change="handlePagination"
			:current-page="currentPage"
			:page-sizes="counts"
			:page-size="mediaCount"
			layout="slot, sizes, ->, prev, pager, next"
			:total="total"
		>
			<slot>
				<span class="show-text">Show</span>
			</slot>
		</el-pagination>
	</el-row>
</div>
</template>
<script>
import Results from 'components/media/Results';

export default {

	props: {
		results: {},
		view: {},
		columnCount: {},
		pickerMode: {},
		allowMultiple: {},
		counts: {
			type: Array,
			default: () => [20, 50, 100, 200]
		},
		filter: {},
		pickerAction: {}
	},

	components: {
		Results
	},

	data() {
		return {
			currentPage: 1,
			mediaCount: this.counts[0]
		};
	},

	computed: {

		filteredResults() {
			return this.filter ? this.filter(this.results) : this.results;
		},

		pagedResults() {
			const
				from = (this.currentPage - 1) * this.mediaCount,
				to = from + this.mediaCount;

			return (
				this.total < (to - from) ?
					this.filteredResults : this.filteredResults.slice(from, to)
			);
		},

		total() {
			return this.results.length;
		}
	},

	methods: {
		handleMediaCountChange(newSize) {
			this.mediaCount = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		}
	}
};
</script>