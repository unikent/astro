<template>
	<div>
		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Pages saved successfully!</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Your page has now been saved!
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary">View Page</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal">Continue Editing</button>
					</div>
				</div>
			</div>
		</div>

		<draggable :list="page"  @start="drag=true" @end="drag=false" v-if="page && globalConfig" :options="{handle:'.card-header'}">
			<block v-if="page.length" v-for="block in page" :key="block.blockorderid" :definition="globalConfig" :data="block"></block>
		</draggable>

		<button @click="addNew">Add New</button>
		<button @click="postPage">Update</button>
	</div>

</template>

<script>
	import Vue from 'vue';
	import Block from './Block.vue';
	import draggable from 'vuedraggable';
	import axios from 'axios';

	export default {
		components : {
			Block,
			draggable,
		},

		data() {
			return {
				globalConfig: [],
				page: [],
				pageid: ''
			}
		},

		methods: {
			getPage() {
				axios
					.get('/api/page/'+this.pageid)
					.then((response) => {
						this.page = response.data;
					});
			},

			getConfig() {
				axios
					.get('/api/config')
					.then((response) => {
						this.globalConfig = response.data;
					});
			},

			postPage() {
				axios
					.put('/api/page/'+this.pageid, this.page)
					.then((response) => {
						console.log(response);
						$('#myModal').modal('toggle')
					});
			},

			addNew() {
				this.page.push(
					{
						"page_id": this.pageid,
						"parent_block": 0,
						"order": 0,
						"section": 0,
						"type_guid": "97a2e1b5-4804-46dc-9857-4235bf76a058",
						"fields": {
							"image": null,
							"block_heading": "Title block",
							"block_description": "<p>New Field</p>",
							"block_link": "<p>Something else</p>",
							"image_alignment": ""
						},

						"blockorderid": 2
					}
				);
			}
		},

		created(value) {
			this.pageid = $('.pageid').attr('id');
			this.getConfig();
			this.getPage();
		}

	}


</script>

<style>


</style>