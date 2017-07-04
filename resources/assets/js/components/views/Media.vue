<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Media Manager</span>

		<div class="u-mla u-flex">

			<el-button
				class="upload-button"
				@click="showUploadForm = true"
			>Upload</el-button>

			<el-input
				placeholder="Search for media"
				class="manage-table__search"
				v-model="searchTerm"
				icon="search"
				:on-icon-click="search"
			/>

		</div>
	</div>

	<el-row>

		<el-col :span="18">

			<el-select class="media__filter-by" placeholder="Filter by" value="">
				<el-option label="All media" value="media" />
				<el-option label="Image" value="image" />
				<el-option label="Document" value="document" />
				<el-option label="Video" value="video" />
				<el-option label="Audio" value="audio" />
			</el-select>

			<el-select class="media__order-by" placeholder="Order by" value="">
				<el-option label="Date added" value="creation">
					<span class="media__order-attr">Date Added</span>
					<span class="media__order-sort">asc</span>
				</el-option>
				<el-option label="Name" value="name" />
				<el-option label="Resolution" value="resolution" />
				<el-option label="Duration" value="duration" />
			</el-select>

		</el-col>

		<el-col :span="6">
			<el-row>
				<el-col :span="12">
					<el-slider
						v-model="imageSize"
						:step="25"
						:show-tooltip="false"
						style="margin: 0 5px;"
					/>
				</el-col>
				<el-col :span="12">
					<el-pagination
						@size-change="handleMediaCountChange"
						:page-sizes="[20, 50, 100, 200]"
						:page-size="mediaCount"
						layout="slot, sizes"
						style="margin: 0"
					>
						<slot>
							<span class="show-text">Show</span>
						</slot>
					</el-pagination>
				</el-col>
			</el-row>
		</el-col>

	</el-row>

	<el-row style="margin-top: 30px">
		<div v-for="rowIndex in Math.ceil(filteredImages.length / colCount)" class="columns">
			<div v-for="colIndex in colCount" class="column">
				<div
					v-if="getThumbnail(rowIndex, colIndex)"
					class="image-grid__item"
					:title="getThumbnail(rowIndex, colIndex).title"
				>
					<lazy-img
						class="img-grid"
						:bg="true"
						:src="getThumbnail(rowIndex, colIndex).url"
						:smallSrc="getThumbnail(rowIndex, colIndex).url"
					/>
					<div class="image-grid__item-overlay" />
					<div class="item-grid__edit">
						<i class="el-icon-edit it-butt" @click="showMediaDetails = true; media = getThumbnailIndex(rowIndex, colIndex)"></i>
						<el-dropdown trigger="click" menu-align="start">
							<i class="el-icon-more"></i>
							<el-dropdown-menu slot="dropdown">
								<el-dropdown-item>Refresh Thumbnails</el-dropdown-item>
								<el-dropdown-item>Download</el-dropdown-item>
								<el-dropdown-item>Delete</el-dropdown-item>
							</el-dropdown-menu>
						</el-dropdown>
					</div>
				</div>
			</div>
		</div>
	</el-row>

	<el-row>
		<el-pagination
			@size-change="handleMediaCountChange"
			@current-change="handlePagination"
			:current-page="currentPage"
			:page-sizes="[20, 50, 100, 200]"
			:page-size="mediaCount"
			layout="total, slot, sizes, ->, prev, pager, next"
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

	<el-dialog title="Details" v-model="showMediaDetails" size="large">
		<div v-if="filteredImages[media]" class="columns">
			<div class="column">
				<div class="preview-dialog__image-outer">
					<div class="preview-dialog__image-wrapper">
						<div class="preview-dialog__image-img" :style="`
							background: url('${filteredImages[media].url}') center center / contain no-repeat;
						`" />
					</div>
				</div>
			</div>
			<div class="column is-one-third">
				<div class="preview-dialog__details-wrapper">
					<div v-for="(value, key) in getAttributes(filteredImages[media])" v-if="value">
						<h3>{{ prettyName(key) }}</h3>
						<p>{{ transformAttr(key, value) }}</p>
					</div>
					<div v-if="filteredImages[media].colours">
						<h3>Colour palette</h3>
						<div class="preview-dialog__details-colour-wrapper">
							<div v-for="rgb in filteredImages[media].colours" class="preview-dialog__details-colours" :style="`background-color: rgb(${rgb.join(',')})`" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</el-dialog>
</el-card>
</template>

<script>
import _ from 'lodash';
import ColorThief from 'color-thief-standalone';
import MediaUpload from '../MediaUpload';
import LazyImg from '../LazyImage';

/* global Image */

export default {

	components: {
		MediaUpload,
		LazyImg
	},

	data() {
		return {
			searchTerm: '',
			showUploadForm: false,
			showMediaDetails: false,
			imageSize: 25,

			currentPage: 1,
			mediaCount: 20,

			images: [],

			media: 0
		};
	},

	watch: {
		showMediaDetails(show) {
			if(show) {
				const img = new Image();

				img.addEventListener('load', () => {
					const colorThief = new ColorThief();
					this.filteredImages.splice(this.media, 1, {
						...this.filteredImages[this.media],
						colours: colorThief.getPalette(img, 6)
					});
				});

				img.src = this.filteredImages[this.media].url;
			}
		}
	},

	created() {
		this.prettyNames = {
			'filename'    : 'Filename',
			'filesize'    : 'File size',
			'type'        : 'File type',
			'height'      : 'Height',
			'width'       : 'Width',
			'aspect_ratio': 'Aspect ratio'
		};
	},

	mounted() {
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
				this.total < to ? this.images : this.images.slice(from, to)
			);
		},

		total() {
			return this.images.length;
		}
	},

	methods: {
		search() {
			// TODO: filter media by "this.searchTerm"
		},

		getThumbnail(row, col) {
			return this.filteredImages[this.getThumbnailIndex(row, col)];
		},

		getThumbnailIndex(rowIndex, colIndex) {
			return (rowIndex - 1) * this.colCount + colIndex - 1;
		},

		handleMediaCountChange(newSize) {
			this.mediaCount = newSize;
		},

		handlePagination(pageNumber) {
			this.currentPage = pageNumber;
		},

		getAttributes(media) {
			return _.pick(media, [
				'type', 'filename', 'filesize',
				'height', 'width', 'aspect_ratio'
			]);
		},

		prettyName(attr) {
			return this.prettyNames[attr] ? this.prettyNames[attr] : attr;
		},

		formatBytes(size, precision = 2) {
			const
				base = Math.log(size) / Math.log(1024),
				floored = Math.floor(base),
				unit = ['bytes', 'KB', 'MB', 'GB', 'TB'][floored],
				formattedBytes = Math.pow(1024, base - floored).toFixed(precision);

			return `${formattedBytes} ${unit}`;
		},

		transformAttr(name, attr) {
			switch(name) {
				case 'filesize':
					return this.formatBytes(attr);
			}

			return attr;
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