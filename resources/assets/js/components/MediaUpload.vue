<template>
	<div>
		<div class="media-upload__type">
			Upload type
			<el-select
				class="media-upload__select"
				v-model="uploadType"
				@change="handleChange"
				placeholder="Filter files"
			>
				<el-option label="All" value="media" />
				<el-option label="Image" value="image" />
				<el-option label="Document" value="document" />
				<el-option label="Video" value="video" />
				<el-option label="Audio" value="audio" />
			</el-select>
		</div>
		<upload-form :accept="accept" :multiple="multiple" />
	</div>
</template>

<script>
import UploadForm from 'components/UploadForm';

export default {

	props: {
		multiple: {
			default: true
		}
	},

	components: {
		UploadForm
	},

	data() {
		return {
			uploadType: 'media',
			accept: '*/*'
		};
	},

	methods: {

		handleChange(val) {
			switch(val) {
				case 'image':
					this.accept = '.jpg,.jpeg,.png,.gif,.bmp,.svg';
					break;
				case 'document':
					this.accept = '.pdf,.doc,.docx,.key,.ppt,.pptx,.pps,.ppsx,.odt,.xls,.xlsx';
					break;
				case 'video':
					this.accept = '.mp4,.m4v,.mov,.wmv,.avi,.mpg,.ogv';
					break;
				case 'audio':
					this.accept = '.mp3,.m4a,.ogg,.wav';
					break;
				default:
					this.accept = '*/*';
			}
		}

	}
};
</script>