<style lang="scss">
.el-upload,
.el-dragger {
	width: 100%;
}
</style>

<template>
	<el-upload
		action=""
		type="drag"
 		:thumbnail-mode="true"
		:on-preview="handlePreview"
		:on-remove="handleRemove"
		:on-success="handleSuccess"
		:on-error="handleError"
		:default-file-list="fileList"
	>
	<i class="el-icon-upload"></i>
	<div class="el-dragger__text">Drop file here or <em>click to upload</em></div>
	<div class="el-upload__tip" slot="tip">jpg/png files sized 500kb or less</div>
</el-upload>
</template>

<script>
	import { mapActions } from 'vuex';

	export default {

		props: ['name'],

		computed: {
			fileList() {
				return [
					{
						name: 'face.png',
						url: 'http://fancy-blocks.site/face.png'
					}
				];
			}
		},

		methods: {

			...mapActions([
				'updateValue'
			]),

			handleRemove(file, fileList) {
				console.log(file, fileList);
			},

			handlePreview(file) {
				console.log(file);
			},

			handleSuccess(file) {
				console.log(file);
			},

			handleError(file) {
				console.log(file);
			}

		}
	}
</script>