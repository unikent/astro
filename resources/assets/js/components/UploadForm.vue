<style lang="scss">
.el-upload-list__item.is-fail .el-icon-cross,
.el-upload-list__item.is-fail .el-icon-circle-cross {
	color: #ff7348;
}

.el-upload-list__item.is-fail:hover .el-icon-cross,
.el-upload-list__item.is-fail:hover .el-icon-circle-cross,
.el-upload-list__item.is-fail .el-icon-close {
	display: none;
}
.el-upload-list__item.is-fail:hover .el-icon-close {
	display: inline-block;
	cursor: pointer;
	opacity: .75;
	-webkit-transform: scale(0.7);
	transform: scale(0.7);
	color: #48576a;
}
.el-upload-list__error {
	padding: 10px;
	color: #d65757;
}
</style>

<template>
	<el-upload
		class="upload-demo"
		drag
		action="media"
		name="upload"
		:on-error="handleError"
		:on-progress="handleProgress"
		:http-request="upload"
		multiple
		:accept="accept"
		style="margin: 0 5px;"
	>
		<i class="el-icon-upload"></i>
		<div class="el-upload__text">Drop file(s) here or <em>click to upload</em></div>
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

// TODO: while uploading stop visiting to different page?.

export default {
	props: [
		'accept'
	],

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
			return upload(options);
		}
	}
};
</script>