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
			:initialValue="field.default"></editor>

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
import 'tinymce/plugins/link/plugin';
import 'tinymce/icons/default/icons.js';
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
			editorConfig: {
				skin_url: Config.get('base_url') + '/build/css/tinymce/skins/ui/oxide',
				content_css: false,
				plugins: 'table link',
				menubar: false,
				toolbar: 'bold italic link | alignleft aligncenter alignright | undo redo | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablemergecells tablesplitcells',
				table_toolbar: '', // disables the popup toolbar
				table_appearance_options: false, // disables editing of Cell spacing, Cell padding, Border and Caption 
				table_advtab: false, // disables table advanced tab
				table_cell_advtab: false, // disables cell advanced tab
				table_row_advtab: false, // disables row advanced tab
				table_resize_bars: false, // disables resizing of table rows and columns
				// if a class_list has been passed in, ensure it is in the right format before passing it on to tinyMCE
				invalid_styles: 'width height', // disable width and height being set on table elements
				table_class_list: this.field.class_list === void 0 ? [] : this.field.class_list.map(cssClass => {
					if (typeof cssClass !== 'object') {
						cssClass = {
							"title": cssClass,
							"value": cssClass
						}
					}
					return cssClass;
				}),
				init_instance_callback: editor => {
					// strip away any content after the table has been processed into html
					editor.on('PostProcess', e => {
						let table = e.content.match(/<table(.|\s)+<\/table>/gm);
						e.content = table && table[0] ? table[0] : this.field.default;
					});
				}
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
