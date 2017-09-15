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


	<div class="el-table w100 el-table--fit el-table--striped el-table--border el-table--enable-row-hover">
		<table cellspacing="0" cellpadding="0" border="0" class="w100">
				<tr>
					<th>
						<div class="cell">
							Name
						</div>
					</th>

					<th>
						<div class="cell">
							Location
						</div>
					</th>

					<th>
						<div class="cell">
							Actions
						</div>
					</th>

				</tr>
			</thead>
			<tbody v-loading.body="loading">
				<tr
				v-for="site in sites" 
				:key="site.id"
				class="el-table__row"
				:class="{ 'el-table__row--selected': selected && selected.indexOf(row.id) !== -1 }"
				>
				<td>
					<div class="cell">
						<router-link :to="`/site/${site.id}/page/${site.homepage.id}`">{{site.name}}</router-link>
					</div>
				</td>

				<td>
					<div class="cell">
						<span class="el-tag el-tag--primary">{{site.host}}{{site.path}}</span>
					</div>
				</td>

				<td>
					<div class="cell">
						<el-button @click="askRemove(site.id)" type="default" size="small">
							<icon name="delete" width="14" height="14" />
						</el-button>
					</div>
				</td>


			</tr>
		</tbody>
	</table>
</div>





		
		<el-dialog title="Add Site" v-model="dialogFormVisible">
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
				</el-row>

				<el-row>
					<el-row :span="24" :gutter="10" v-for="error in form.errors" :key="error.id">
						1 - {{error}}
					</el-row>
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
			this.form.errors = [];
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
				.catch((errors) => {
					console.log('API error trying to POST ', site);
					console.log(errors.response.data.errors);
					this.form.errors = errors.response.data.errors;
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