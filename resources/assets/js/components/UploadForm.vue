<template>
	<el-upload
		class="upload-form"
		drag
		action="media"
		name="upload"
		:on-error="handleError"
		:on-progress="handleProgress"
		:on-success="onSuccess"
		:http-request="upload"
		:multiple="multiple"
		:accept="accept"
	>
		<i class="el-icon-upload"></i>
		<div class="el-upload__text">Drop {{ multiple ? 'file(s)' : 'single file' }} here or <em>click to upload</em></div>
		<div class="el-upload__tip" slot="tip">
			Files must be less than 5MB. If this dialog is closed uploads will happen in the background.
			<upload-fail-list
				listType="text"
				:files="failedUploads"
			/>
		</div>
	</el-upload>
</template>

<script>
import upload from '../plugins/http/upload';
import UploadFailList from './UploadFailList';

// TODO: while uploading show global upload progress.

export default {
	props: {
		accept: String,
		multiple: Boolean,
		onSuccess: {
			type: Function,
			default: () => {}
		},
		site_id: Number
	},

	components: {
		UploadFailList
	},

	data() {
		return {
			failedUploads: []
		};
	},

	methods: {
		handleError(err, file, fileList) {
			if(err.response && err.response.status === 422) {
				file.error = err.response.data.errors[0].message;
			}

			this.failedUploads.push(file);
		},

		handleProgress(e, file, fileList) {
			console.log(e, file.uid, fileList);
		},

		upload(options) {
			if(!options.data) {
				options.data = {};
			}
			options.data['site_ids[]'] = this.site_id;
			return upload(options);
		}
	}
};
</script>