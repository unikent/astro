<template>
<el-upload
	class="upload-form"
	drag
	action="media"
	name="upload"
	:on-error="handleError"
	:on-success="handleSuccess"
	:on-remove="handleRemove"
	:on-preview="handlePreview"
	:http-request="upload"
	:accept="accept"
	:file-list="fileList"
	style="margin: 0 5px;"
>
	<i class="el-icon-upload"></i>
	<div class="el-upload__text">Drop a file here or <em>click to upload</em></div>
	<div class="el-upload__tip" slot="tip">
		Files must be less than 20MB.
		<upload-fail-list
			listType="text"
			:files="failedUploads"
		/>
	</div>
</el-upload>
</template>

<script>
import upload from 'plugins/http/upload';
import UploadFailList from '../UploadFailList';

import { mapMutations, mapState } from 'vuex';

export default {

	name: 'image-field',

	components: {
		UploadFailList
	},

	props: [
		'name',
		'accept',
		'multiple'
	],

	data() {
		return {
			showPreview: false,
			failedUploads: []
		};
	},

	computed: {
		fileList: {
			get() {
				const val = this.$store.getters.getCurrentFieldValue(this.name);
				return val ? [{...val, name: val.filename }] : [];
			},
			set(value) {
				this.updateFieldValue({
					name: this.name,
					value
				});

				this.updateBlockMedia({
					value: {
						...value,
						associated_field: this.name
					}
				});
			}
		},

		...mapState([
			'preview'
		])
	},

	methods: {

		...mapMutations([
			'updateFieldValue',
			'updateBlockMedia'
		]),

		handleError(err, file) {
			if(err.response && err.response.status === 422) {
				file.error = err.response.data.errors[0].message;
			}

			this.failedUploads.push(file);
		},

		handleSuccess({ data: json }) {
			this.fileList = json.data;
		},

		upload(options) {
			if(!options.data) {
				options.data = {};
			}

			// TODO: update with real site or publishing group id
			options.data['publishing_group_ids[]'] = 1;

			return upload(options);
		},

		handleRemove() {
			this.fileList = null;
		},

		handlePreview() {
			this.$store.commit('changePreview', {
				visible: true,
				url: this.fileList[0].url
			});
		}

	}
};
</script>