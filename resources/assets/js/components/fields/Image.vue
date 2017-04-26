<style lang="scss">
.upload-demo .el-upload {
	width: 100%;
}
</style>

<template>
<el-upload
	class="upload-demo"
	drag
	action=""
	:on-preview="handlePreview"
	:on-remove="handleRemove"
	:on-success="handleSuccess"
	:on-error="handleError"
	:file-list="fileList"
>
	<i class="el-icon-upload"></i>
	<div class="el-upload__text">Drop file here or <em>click to upload</em></div>
	<div class="el-upload__tip" slot="tip">jpg/png files with a size less than 500kb</div>
</el-upload>
</template>

<script>
import { mapMutations, mapState } from 'vuex';
import _ from 'lodash';

/* global URL */

export default {

	name: 'image-field',

	props: ['name'],

	data() {
		return {
			showPreview: false
		};
	},

	computed: {
		fileList: {
			get() {
				const val = this.$store.getters.getCurrentFieldValue(this.name);
				return val ? [val] : [];
			},
			set(value) {
				this.updateValue({
					name: this.name,
					value: value ? _.pick(value, 'name', 'url') : value
				});
			}
		},

		...mapState([
			'page',
			'preview'
		])
	},

	methods: {

		...mapMutations([
			'updateValue'
		]),

		handleRemove(file, fileList) {
			this.fileList = null;
		},

		handlePreview(file) {
			this.$store.commit('changePreview', {
				visible: true,
				url: this.fileList[0].url
			});
		},

		handleSuccess(file) {
			console.log(file);
		},

		handleError(res, file) {
			this.fileList = {
				name: file.name,
				url: URL.createObjectURL(file.raw)
			};
		}

	}
};
</script>