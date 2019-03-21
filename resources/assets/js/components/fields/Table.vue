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
			Edit table <br>
			<el-button type="text" @click="hideEditTableDialog"><i class="el-icon-arrow-left"></i> Back</el-button>
			<el-button type="text" @click="toggleDialogFullscreen"><i class="el-icon-d-caret"></i> {{ fullscreenToggleText }}</el-button>
		</div>
		
	  	<el-table :data="gridData">
	   		<el-table-column property="date" label="Date" width="150"></el-table-column>
	    	<el-table-column property="name" label="Name" width="200"></el-table-column>
	    	<el-table-column property="address" label="Address"></el-table-column>
	  	</el-table>
	</el-dialog>
</div>
</template>

<script>
import Vue from 'vue';
import BlockField from 'components/BlockField';
import { Dialog } from 'element-ui';

export default {
	name: 'table-field',
	extends: BlockField,
	data() {
		return {
			gridData: [
				{
		          date: '2016-05-02',
		          name: 'John Smith',
		          address: 'No.1518,  Jinshajiang Road, Putuo District'
		        }, {
		          date: '2016-05-04',
		          name: 'John Smith',
		          address: 'No.1518,  Jinshajiang Road, Putuo District'
		        }, {
		          date: '2016-05-01',
		          name: 'John Smith',
		          address: 'No.1518,  Jinshajiang Road, Putuo District'
		        }, {
		          date: '2016-05-03',
		          name: 'John Smith',
		          address: 'No.1518,  Jinshajiang Road, Putuo District'
		        }
	        ],
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