<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Manage sites</span>
		<el-button type="default" @click="dialogFormVisible = true" class="manage-table__add-button">
			Add Site
		</el-button>
	</div>
	<div>
		<!--

		<div class="site-list-pagination">
			<el-pagination
				@current-change="navigate"
				:current-page="pagination.current_page"
				:page-size="pagination.per_page"
				layout="total, prev, pager, next, jumper"
				:total="pagination.total">
			</el-pagination>
		</div>  v-loading.body="fetching"

		-->


		<!--

			<el-row type="flex" justify="center">
			<el-col :span="24">
				 <el-table :data="sites" stripe border class="w100" v-loading.body="loading">
					<el-table-column prop="name" label="Name" width="300"></el-table-column>
					<el-table-column prop="canonical.path" label="Path"></el-table-column>
					<el-table-column inline-template label="Actions" width="110">
						<div>
							<router-link :to="`/site/${sites[$index].id}/page/${sites[$index].homepage.id}`">
								<el-button type="default" size="small">
									<icon name="edit" width="14" height="14" />
								</el-button>
							</router-link>
							<el-button @click="askRemove($index)" type="default" size="small">
								<icon name="delete" width="14" height="14" />
							</el-button>
						</div>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>

	-->

	<h2>Temp Site List</h2>
	<ul v-loading.body="loading">
		<li v-for="site in sites">
			<router-link :to="`/site/${site.id}/page/${site.homepage.id}`">{{site.name}}</router-link>
							<el-button @click="askRemove(site.id)" type="default" size="small">
								<icon name="delete" width="14" height="14" />
							</el-button>
		</li>
	</ul>
		

		<el-dialog title="Site Options" v-model="dialogFormVisible">
			<el-form :model="form" label-position="top">
				<el-row type="flex" :gutter="20">
					<el-col :span="11">

						<el-form-item label="Title">
							<el-input v-model="form.name" auto-complete="off"></el-input>
						</el-form-item>

						<el-form-item label="Domain">
							<el-input v-model="form.host" auto-complete="off" placeholder="www.kent.ac.uk"></el-input>
						</el-form-item>

						<el-form-item label="Path">
							<el-input v-model="form.path" auto-complete="off" placeholder="/"></el-input>
						</el-form-item>

					</el-col>

					<el-col :span="11" :offset="2">
						<el-form-item label="Home page layout">

							<el-select v-model="form.homepage_layout" class="w100" placeholder="Select">
								<el-option v-for="layout in layouts" :label="layout.name" :value="layout" :key="layout.name" />
								<!-- <el-option label="Default" value="" /> -->
							</el-select>
						</el-form-item>

					</el-col>

					<p>{{errors}}</p>
				</el-row>
			</el-form>
			<span slot="footer" class="dialog-footer">
				<el-button @click="dialogFormVisible = false">Cancel</el-button>
				<el-button type="primary" @click="addSite">Add Site</el-button>
			</span>
		</el-dialog>

	</div>


</el-card>

</template>

<script>
import Icon from 'components/Icon';

export default {

	components: {
		Icon
	},

	data() {
		return {
			sites: [],
			layouts: [],
			dialogFormVisible: false,
			loading: true,

			form: {
				name: '',
				path: '',
				host: '',
				homepage_layout: [],
				publishing_group_id: '',
				errors: '',
			}
		};
	},

	created() {
		this.fetchData();
	},


	methods: {

		askRemove(index) {
			this.$confirm(
				`Site ${index} will be permanently removed.\nAre you sure?`,
				'Warning',
				{
					confirmButtonText: 'OK',
					cancelButtonText: 'Cancel',
					type: 'warning'
				}
			).then(() => {
				this.$message({
					type: 'success',
					message: 'Delete completed'
				});
			}).catch(() => {});
		},

		addSite() {

			this.loading = true;

			let site = {};
			site = ({
			  name: this.form.name,
			  host: this.form.host,
			  path: this.form.path,
			  publishing_group_id: "1",
			  homepage_layout: {
			  	name: this.form.homepage_layout.name,
			  	version: this.form.homepage_layout.version
			  }
			});

			console.log(site);
			this.$api
				.post('sites', site)
				.then((response) => {
					// success, so let's refresh what data as have from the api
					this.fetchData();

					// reset the form
					this.form = {
						name: '',
						host: '',
						path: '',
						errors: '',
						publishing_group_id: '',
						homepage_layout: []
					};
					this.loading = false;
					this.dialogFormVisible = false;
				})
				.catch((response) => {
					console.log('API error trying to POST ', site);
					this.form.errors = response;
					// alert('fix this issues dude');
					this.loading = false;
				});



				
		},



		fetchData() {
			let layouts = {};
			this.$api
				.get('sites?include=homepage.revision')
				.then((response) => {
					this.sites = response.data.data;
				});

			this.$api
				.get('layouts/definitions')
				.then((response) => {
					this.layouts = [];
					layouts = response.data.data;
					for (var i = layouts.length - 1; i >= 0; i--) {
						// @TODO - this should return an array of layout definations 
						// so for now we are faking this and setting the version numbers to 1
						let currentLayout = [];
						currentLayout.name = layouts[i];
						currentLayout.version = "1";
						this.layouts.push(currentLayout);
					}
					this.loading = false;
				});

		}

	}
};
</script>