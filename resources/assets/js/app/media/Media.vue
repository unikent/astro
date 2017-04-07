<style lang="scss">
.manage-table__search {
	max-width: 250px;
}
h3 {
	margin-top: 0;
}
.image-grid__item {
	background-color: #d9dee2;
	position: relative;
	padding-top: 100%;
	border: 1px solid #d1dbe5;
	border-radius: 2px;
	transition: transform .2s ease-out, border .2s ease-out, box-shadow .2s ease-out;
	overflow: hidden;
}
.image-grid__item .img-grid {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
}

.image-grid__item-overlay {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	opacity: 0;
	background-color: #204869;
	background: radial-gradient(farthest-corner at 50% 50%, rgba(50, 50, 50, .5) 50%, #323232 100%);
	transition: opacity .3s ease-out;
}

.item-grid__edit {

	transform: translateY(40%);
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;

	opacity: 0;
	transition: transform .2s ease-out, opacity .2s ease-out;

	.it-butt {
		position: absolute;
		left: 10px;
		bottom: 10px;
		font-size: 1.2rem;
		color: #fff;
		cursor: pointer;
	}

	.el-dropdown {
		position: absolute;
		right: 10px;
		bottom: 5px;
		color: #fff;
		font-size: 1.2rem;
		cursor: pointer;
	}
}

.image-grid__item:hover  {
	// transform: scale(1.02);
	transform-origin: 50%;
	border: 1px solid #3b6586;
	box-shadow: 0 1px 2px 0 rgba(0,0,0,0.1), 0 4px 8px 0 rgba(0,0,0,0.2);

	.image-grid__item-overlay {
		opacity: .6;
	}

	.item-grid__edit {
		opacity: 1;
		transform: translateY(0);
	}
}

.el-pagination {
	margin-top: 2em;
}
.el-pagination .el-select .el-input {
	width: 68px;
}
.el-pagination .show-text {
	margin: 0 10px 0 20px;
}

.u-flex {
	display: flex;
}

.upload-button {
	margin-right: 14px;
}
</style>

<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Media Manager</span>

		<div class="u-mla u-flex">

			<el-button class="upload-button" @click="showUploadForm = true">
				Upload
			</el-button>

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
					:title="getImage(rowIndex, colIndex).title">
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
import UploadForm from '../../components/UploadForm';
import LazyImg from '../../components/LazyImage';

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
		search(e) {
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