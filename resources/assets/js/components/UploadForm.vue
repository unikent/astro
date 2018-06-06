<template>
	<el-upload
		class="upload-form"
		drag
		action="media"
		name="upload"
		:on-error="handleError"
		:on-success="onSuccess"
		:http-request="upload"
		:multiple="multiple"
		:accept="accept"
	>
		<i class="el-icon-upload"></i>
		<div class="el-upload__text">Drop {{ multiple ? 'one or more files' : 'a single file' }} here or <em>click to upload</em></div>
		<div class="el-upload__tip" slot="tip">
			Files must be less than 5MB. 
			<template v-if="imageHelp">
				<a class="el-upload__tip--link" :href="imageHelp" target="_blank">Need help? See our image guidelines ></a>
			</template>
			<template v-else>
			If this dialog is closed uploads will happen in the background.
			</template>
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
import Config from 'classes/Config.js';

// TODO: while uploading show global upload progress.

export default {
	props: {
		accept: String,
		multiple: Boolean,
		onSuccess: {
			type: Function,
			default: () => {}
		},
		siteId: Number
	},

	components: {
		UploadFailList
	},

	data() {
		return {
			failedUploads: []
		};
	},

	computed: {
		imageHelp() {
			return Config.get('help_media_url') || false
		}
	},

	methods: {
		handleError(err, file) {
			if(err.response && err.response.data && err.response.data.errors && err.response.data.errors.length) {
				file.error = err.response.data.errors[0].message;
			}

			this.failedUploads.push(file);
		},

		upload(options) {
			if(!options.data) {
				options.data = {};
			}
			options.data['site_ids[]'] = this.siteId;
			return upload(options);
		}
	}
};
</script>