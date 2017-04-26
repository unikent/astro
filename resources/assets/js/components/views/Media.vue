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
			<el-select style="max-width: 120px" placeholder="Filter by">
				<el-option label="All media" value="media" />
				<el-option label="Image" value="image" />
				<el-option label="Document" value="document" />
				<el-option label="Video" value="video" />
				<el-option label="Audio" value="audio" />
			</el-select>

			<el-select style="max-width: 140px" placeholder="Order by">
				<el-option label="Date added" value="media">
					<span style="float: left">Date Added</span>
					<span style="float: right; color: #8492a6; font-size: 13px">asc</span>
				</el-option>
				<el-option label="Name" value="image" />
				<el-option label="Rating" value="document" />
				<el-option label="Resolution" value="video" />
				<el-option label="Duration" value="audio" />
			</el-select>

		</el-col>

		<el-col :span="6">
			<el-row>
				<el-col :span="12">
					<el-slider
						v-model="imageSize"
						:step="25"
						style="margin: 0 5px;"
					/>
				</el-col>
				<el-col :span="12">
					<el-pagination
						@size-change="changeSize"
						:page-sizes="[20, 50, 100, 200]"
						:page-size="size"
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
					v-if="getImage(rowIndex, colIndex)"
					class="image-grid__item"
					:title="getImage(rowIndex, colIndex).title"
				>
					<lazy-img
						class="img-grid"
						:bg="true"
						:src="getImage(rowIndex, colIndex).src"
						:smallSrc="getImage(rowIndex, colIndex).src"
					/>
					<div class="image-grid__item-overlay" />
					<div class="item-grid__edit">
						<i class="el-icon-edit it-butt" @click="showMediaDetails = true; media = getThumbIndex(rowIndex, colIndex)"></i>
						<el-dropdown trigger="click" menu-align="start">
							<i class="el-icon-more"></i>
							<el-dropdown-menu slot="dropdown">
								<el-dropdown-item>Refresh Thumbnails</el-dropdown-item>
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
			@size-change="changeSize"
			@current-change="changeCurrent"
			:current-page="current"
			:page-sizes="[20, 50, 100, 200]"
			:page-size="size"
			layout="total, slot, sizes, ->, prev, pager, next"
			:total="total"
		>
			<slot>
				<span class="show-text">Show</span>
			</slot>
		</el-pagination>
	</el-row>

	<el-dialog :title="`Upload ${uploadType}`" v-model="showUploadForm">
		<div style="margin-bottom: 20px; text-align: right">
			Upload type
			<el-select style="max-width: 120px" v-model="uploadType" placeholder="Filter files" @change="handleChange">
				<el-option label="All" value="media" />
				<el-option label="Image" value="image" />
				<el-option label="Document" value="document" />
				<el-option label="Video" value="video" />
				<el-option label="Audio" value="audio" />
			</el-select>
		</div>
		<upload-form :accept="accept" />
	</el-dialog>

	<el-dialog title="Details" v-model="showMediaDetails" size="large">
		<div v-if="filteredImages[media]" class="columns">
			<div class="column">
				<img :src="filteredImages[media].src" />
			</div>
			<div class="column is-one-third">

			</div>
		</div>
	</el-dialog>
</el-card>
</template>

<script>
import UploadForm from '../UploadForm';
import LazyImg from '../LazyImage';

export default {

	components: {
		UploadForm,
		LazyImg
	},

	data() {
		return {
			searchTerm: '',
			showUploadForm: false,
			showMediaDetails: false,
			accept: '*/*',
			uploadType: 'media',
			imageSize: 25,

			current: 1,
			size: 20,

			images: [],

			media: 0
		};
	},

	mounted() {
		this.fetchData();
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
			const start = (this.current - 1) * this.size;
			return this.images.slice(start, start + this.size);
		},

		total() {
			return this.images.length;
		}
	},

	methods: {
		search() {
			console.log('Search for files', this.searchTerm);
		},

		handleChange(val) {
			switch(val) {
				case 'image':
					this.accept = '.jpg,.jpeg,.png,.gif,.bmp,.svg';
					break;
				case 'document':
					this.accept = '.pdf,.doc,.docx,.key,.ppt,.pptx,.pps,.ppsx,.odt,.xls,.xlsx,.zip';
					break;
				case 'video':
					this.accept = '.mp3,.m4a,.ogg,.wav,.mp4';
					break;
				case 'audio':
					this.accept = '.mp4,.m4v,.mov,.wmv,.avi,.mpg,.ogv';
					break;
				default:
					this.accept = '*/*';
			}

			console.log(this.accept);
		},

		updateColumnCount(num) {
			this.colCount = num;
		},

		getImage(row, col) {
			return this.filteredImages[this.getThumbIndex(row, col)];
		},

		getThumbIndex(rowIndex, colIndex) {
			return (rowIndex - 1) * this.colCount + colIndex - 1;
		},

		changeSize(newSize) {
			this.size = newSize;
		},

		changeCurrent(newCurrent) {
			this.current = newCurrent;
		},

		fetchData() {
			this.$api
				.get('media')
				.then((response) => {
					this.images = response.data;
				});
		}

	}
};
</script>