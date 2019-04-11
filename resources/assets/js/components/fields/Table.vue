<template>
<div class="table-field">
	<el-button type="primary" @click="showEditTableDialog">Edit table</el-button>
	<el-dialog 
		custom-class="edit-table-dialog"
		:visible.sync="editTableDialogVisible" 
		:append-to-body="true" 
		:fullscreen="editTableDialogFullscreen"
		top="8vh">
		<div slot="title">
			Edit table
			<el-button type="text" @click="toggleDialogFullscreen"><i class="el-icon-d-caret"></i> {{ fullscreenToggleText }}</el-button>
		</div>

	  	<editor  
			v-model="value"
			v-if="editTableDialogVisible"
			:init="editorConfig"
			:initialValue="editorData"></editor>

		<span slot="footer" class="dialog-footer">
			<el-button type="primary" @click="hideEditTableDialog">Close</el-button>
		</span>
	</el-dialog>
</div>
</template>

<script>
import Vue from 'vue';
import BlockField from 'components/BlockField';
import { Dialog } from 'element-ui';
import tinyMCE from 'tinymce';
import 'tinymce/themes/silver/theme';
import 'tinymce/plugins/table/plugin';
import Editor from '@tinymce/tinymce-vue';
import Config from '../../classes/Config';

export default {
	name: 'table-field',
	extends: BlockField,
	components: {
		'editor': Editor
	},
	data() {
		return {
			editorData: '<table><tr><td></td></td></tr><tr><td></td></td></tr></table>',
			editorConfig: {
				skin_url: Config.get('base_url') + '/build/css/tinymce/skins/ui/oxide',
				content_css: false,
				plugins: 'table',
				menubar: false,
				toolbar: 'bold italic link | alignleft aligncenter alignright | undo redo | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablemergecells tablesplitcells'
			},
			editTableDialogVisible: false,
			editTableDialogFullscreen: false
		}
	},

	computed: {
		fullscreenToggleText() {
			return this.editTableDialogFullscreen ? 'Disable fullscreen' : 'Enable fullscreen';
		}
	},
	methods: {
		showEditTableDialog() {
			this.editTableDialogVisible = true;
		},
		hideEditTableDialog() {
			this.editTableDialogVisible = false;
		},
		toggleDialogFullscreen() {
			this.editTableDialogFullscreen = !this.editTableDialogFullscreen;
		}
	}
};
</script>
<style>
	.tox.tox-tinymce-aux {
		z-index: 3000;
	}
</style>
