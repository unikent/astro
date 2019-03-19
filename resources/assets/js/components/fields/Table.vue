<template>
<div class="table-field">
	<el-button type="primary" @click="showEditTableDialog">Edit table</el-button>
	<el-dialog title="Shipping address" :visible.sync="dialogTableVisible" :modal-append-to-body="true" :fullscreen="true">
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
	        dialogTableVisible: false
	    }
	},
	methods: {
		showEditTableDialog() {
			// DailogMaker = new Vue.extend(Dialog);
			// console.log(DailogMaker);
			// this.dialog = new DailogMaker(
			// 	// {
			// 	// 	target: this.$el,
			// 	// 	visible:true
			// 	// }
			// );
			//this.dialog.$mount('body');
			this.dialogTableVisible = true;
		}
	}
};
</script>