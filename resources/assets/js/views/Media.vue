<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Media Manager</span>

		<div class="u-mla u-flex">

			<el-button @click="showUploadForm = true">Upload</el-button> <!-- class="upload-button" -->

			<!-- <el-input
				placeholder="Search for media"
				class="manage-table__search"
				v-model="searchTerm"
				icon="search"
				:on-icon-click="search"
			/> -->

		</div>
	</div>

	<div class="columns">

		<div class="column is-three-quarters">
			<!-- <el-select class="media__filter-by" placeholder="Show" value="media">
				<el-option label="Show all" value="media" />
				<el-option label="Image" value="image" />
				<el-option label="Document" value="document" />
				<el-option label="Video" value="video" />
				<el-option label="Audio" value="audio" />
			</el-select>
			<el-select class="media__filter-by" placeholder="Filter by" value="">
				<el-option label="All media" value="media" />
				<el-option label="Unused" value="media" />
			</el-select>

			<el-select class="media__order-by" placeholder="Order by" value="">
				<el-option label="Date added" value="creation">
					<span class="media__order-attr">Date Added</span>
					<span class="media__order-sort">asc</span>
				</el-option>
				<el-option label="Name" value="name" />
				<el-option label="Resolution" value="resolution" />
				<el-option label="Duration" value="duration" />
			</el-select> -->
			<span style="line-height: 36px">{{ total }} results</span>
		</div>

		<div class="column">
			<el-slider
				v-show="view === 'Grid'"
				v-model="imageSize"
				:step="25"
				:show-tooltip="false"
			/>
		</div>

		<div class="column o-media__switch">
			<el-radio-group v-model="view" size="small">
				<el-radio-button label="Grid"></el-radio-button>
				<el-radio-button label="Details"></el-radio-button>
			</el-radio-group>
		</div>

	</div>

<!-- 	<el-row>
		<span>Showing all media filtered by "" in descending order of duration</span>
		<span class="results-text">{{ total }} results</span>
	</el-row> -->

	<el-row>
		<results
			:results="filteredImages"
			:view="view"
			:columnCount="colCount"
			:editAction="showMediaOverlay"
		/>
	</el-row>

	<el-row>
		<el-pagination
			@size-change="handleMediaCountChange"
			@current-change="handlePagination"
			:current-page="currentPage"
			:page-sizes="[20, 50, 100, 200]"
			:page-size="mediaCount"
			layout="slot, sizes, ->, prev, pager, next"
			:total="total"
		>
			<slot>
				<span class="show-text">Show</span>
			</slot>
		</el-pagination>
	</el-row>

	<el-dialog title="Upload Media" v-model="showUploadForm">
		<media-upload />
	</el-dialog>

	<media-overlay />
</el-card>
</template>

<script>
import { mapActions } from 'vuex';

import Results from 'components/media/Results';
import MediaUpload from 'components/MediaUpload';
import MediaOverlay from 'components/media/MediaOverlay';

export default {

	components: {
		MediaUpload,
		Results,
		MediaOverlay
	},

	data() {
		return {
			searchTerm: '',
			showUploadForm: false,
			imageSize: 25,

			currentPage: 1,
			mediaCount: 20,

			images: [],
			view: 'Grid'
		};
	},

	created() {
		this.fetchMedia();
	},

	computed: {
		colCount() {
			switch(this.imageSize) {
				case 0:
					return 8;
				case 25:
					return 6;
				case 50:
					return 5;
				case 75:
					return 4;
				case 100:
					return 3;
			}
		},

		filteredImages() {
			const
				from = (this.currentPage - 1) * this.mediaCount,
				to = from + this.mediaCount;
			return (
				this.total < (to - from) ? this.images : this.images.slice(from, to)
			);
		},

		total() {
			return this.images.length;
		}
	},

	methods: {
		...mapActions([
			'showMediaOverlay'
		]),

		search() {
			// TODO: filter media by "this.searchTerm"
		},

		handleMediaCountChange(newSize) {
			this.mediaCount = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		},

		fetchMedia() {
			this.$api
				.get('media')
				.then(({ data: json }) => {
					this.images = json.data;
				});
		}

	}
};
</script>