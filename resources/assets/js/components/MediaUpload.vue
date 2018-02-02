<template>
	<div>
		<div v-if="allowTypes" class="media-upload__type">
			Upload type
			<el-select
				class="media-upload__select"
				v-model="uploadType"
				@change="changeAccept"
				placeholder="Filter files"
			>
				<el-option label="Any" value="media" />
				<el-option label="Image" value="image" />
				<el-option label="Document" value="document" />
				<el-option label="Video" value="video" />
				<el-option label="Audio" value="audio" />
			</el-select>
		</div>
		<upload-form
			:accept="accept"
			:multiple="multiple"
			:on-success="onSuccess"
			:site-id="Number(this.$route.params.site_id)"
		/>
	</div>
</template>

<script>
import UploadForm from 'components/UploadForm';

export default {

	name: 'media-upload',

	props: {
		multiple: {
			default: true
		},
		allowTypes: {
			default: true
		},
		type: {
			default: 'media'
		},
		onSuccess: {
			type: Function,
			default: () => {}
		},
		siteId: Number
	},

	components: {
		UploadForm
	},

	created() {
		if(!this.allowTypes) {
			this.changeAccept(this.type);
		}
	},

	data() {
		return {
			uploadType: 'media',
			accept: '*/*'
		};
	},

	methods: {

		changeAccept(val) {
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