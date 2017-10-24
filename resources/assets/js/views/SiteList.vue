<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Manage sites</span>
		<el-button type="default" @click="dialogFormVisible = true" class="manage-table__add-button">
			Add Site
		</el-button>
	</div>

	<div class="el-table w100 el-table--fit el-table--striped el-table--border el-table--enable-row-hover">
		<table cellspacing="0" cellpadding="0" border="0" class="w100">
			<thead>
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
							<router-link :to="`/site/${site.id}/menu`">
								<el-button type="default" size="small">
									Menu
								</el-button>
							</router-link>
							<router-link :to="`/site/${site.id}/users`">
								<el-button type="default" size="small">
									Manage users
								</el-button>
							</router-link>
							<el-button @click="askRemove(site.id)" type="default" size="small">
								<icon name="delete" width="14" height="14" />
							</el-button>
						</div>
					</td>


				</tr>
			</tbody>
		</table>

		<el-dialog title="Add Site" v-model="dialogFormVisible">
			<el-form :model="form" label-position="top">
				<el-row type="flex" :gutter="20">

					<el-col :span="11">

						<el-form-item label="Name">
							<el-input v-model="form.name" auto-complete="off"></el-input>
						</el-form-item>
					</el-col>

					<el-col :span="11" :offset="2">
						<el-form-item label="Home page layout">
							<el-select v-model="form.homepage_layout" class="w100" placeholder="Select">
								<el-option v-for="layout in layouts" :label="layout.name" :value="layout" :key="layout.name" />
							</el-select>
						</el-form-item>
					</el-col>

				</el-row>

				<el-row type="flex" :gutter="20">

					<el-col :span="11">

						<el-form-item label="Host">
							<el-input v-model="form.host" auto-complete="off" placeholder="www.kent.ac.uk"></el-input>
						</el-form-item>

					</el-col>

					<el-col :span="11" :offset="2">

						<el-form-item label="Path">
							<el-input v-model="form.path" auto-complete="off" placeholder=""></el-input>
						</el-form-item>
					</el-col>

				</el-row>

				<div class="el-alert el-alert--error" v-if="form.errorMsgs">
					<i class="el-alert__icon el-icon-circle-cross is-big"></i>
					<div class="el-alert__content">
						<span class="el-alert__title is-bold">{{form.errorMsgs}}</span>
						<ul v-for="error in form.errorsDetails" :key="error.id">
							<li class="el-alert__description">{{error}}</li>
						</ul>
					</div>
				</div>

			</el-form>
			<span slot="footer" class="dialog-footer">
				<el-button @click="cancelForm">Cancel</el-button>
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
				homepage_layout: '',
				errors: '',
			}
		};
	},

	created() {
		this.fetchData();
	},

	methods: {

		// TODO: delete site is not yet implemented!
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

		cancelForm() {
			this.dialogFormVisible = false;
			// reset the form
			this.form = {
				name: '',
				host: '',
				path: '',
				errors: '',
				homepage_layout: ''
			};
			this.loading = false;
		},

		addSite() {
			this.loading = true;
			this.dialogFormVisible = false;

			// paths needs to be blank or a srting starting with a /
			if (this.form.path.length != 0) {
				if (this.form.path[0] != '/') {
					this.form.path = '/' + this.form.path;
				}
			}
			let site = {};

			site = ({
				name: this.form.name,
				host: this.form.host,
				path: this.form.path,
				homepage_layout: {
					name: this.form.homepage_layout.name,
					version: this.form.homepage_layout.version
				}
			});

			this.form.errors = [];
			this.$api
				.post('sites', site)
				.then(() => {
					// success, so let's refresh what data as have from the api
					this.fetchData();

					// reset the form
					this.form = {
						name: '',
						host: '',
						path: '',
						errors: '',
						homepage_layout: ''
					};
					this.loading = false;

				})
				.catch((errors) => {
					this.dialogFormVisible = true;
					this.form.errorMsgs = errors.response.data.errors[0].message;
					this.form.errorsDetails = errors.response.data.errors[0].details;
					this.loading = false;
				});
		},

		fetchData() {
			const fetchSites = this.$api.get('sites?include=homepage.revision');
			const fetchLayouts = this.$api.get('layouts/definitions');

			// make sure we get all the data back before continuing
			this.$api
				.all([fetchSites, fetchLayouts])
				.then(this.$api.spread((sites, layouts) => {

					this.sites = sites.data.data;
					this.layouts = [];
					layouts = layouts.data.data;

					for(var i = layouts.length - 1; i >= 0; i--) {
						// TODO: this should return an array of layout definitions
						// so for now we are faking this and setting the version numbers to 1
						let currentLayout = [];
						currentLayout.name = layouts[i];
						currentLayout.version = '1';
						this.layouts.push(currentLayout);
					}

					// now we have all the data unhide the list
					this.loading = false;
				}))
				.catch((errors) => {
					// TODO: what do we do when the API is unavaliable
				});
		}
	}
};
</script>
