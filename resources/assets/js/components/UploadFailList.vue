<template>
	<transition-group
		tag="ul"
		:class="['el-upload-list', 'el-upload-list--' + listType]"
		name="list"
	>
		<li
			v-for="file in files"
			:class="['el-upload-list__item', 'is-' + file.status]"
			:key="file.uid"
		>
			<img
				class="el-upload-list__item-thumbnail"
				v-if="['picture-card', 'picture'].indexOf(listType) > -1 && file.status === 'success'"
				:src="file.url" alt=""
			>
			<a class="el-upload-list__item-name" @click="handleClick(file)">
				<i class="el-icon-document"></i>{{file.name}}
			</a>
			<label class="el-upload-list__item-status-label">
				<i :class="{
						'el-icon-circle-cross': listType === 'text',
						'el-icon-check': ['picture-card', 'picture'].indexOf(listType) > -1
				}" />
				<i class="el-icon-close" @click="handleDelete(file)"></i>
			</label>
			<div class="el-upload-list__error">{{ file.error }}</div>
		</li>
	</transition-group>
</template>

<script>
import Locale from 'element-ui/lib/mixins/locale';

export default {
	mixins: [Locale],

	props: {
		files: {
			type: Array,
			default() {
				return [];
			}
		},
		handlePreview: Function,
		listType: String
	},

	methods: {

		parsePercentage(val) {
			return parseInt(val, 10);
		},

		handleClick(file) {
			this.handlePreview && this.handlePreview(file);
		},

		handleDelete(file) {
			this.files.splice(this.files.indexOf(file), 1);
			this.handleRemove && this.handleRemove(file);
		}
	}
};
</script>