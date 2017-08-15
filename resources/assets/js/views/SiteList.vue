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

		<el-dialog title="Site Options" v-model="dialogFormVisible">
			<el-form :model="form" label-position="top">
				<el-row type="flex" :gutter="20">
					<el-col :span="11">
						<el-form-item label="Title">
							<el-input v-model="form.title" auto-complete="off"></el-input>
						</el-form-item>
						<el-form-item label="URL">
							<el-input v-model="form.url" auto-complete="off"></el-input>
						</el-form-item>
						<el-form-item label="Parent site">
							<el-select v-model="form.parent" class="w100">
								<el-option label="Root (none)" :value="0" />
								<el-option v-for="site in sites" :label="site.title" :value="site.id" :key="site.id" />
							</el-select>
						</el-form-item>
					</el-col>
					<el-col :span="11" :offset="2">
						<el-form-item label="Home page layout">
							<el-select v-model="form.layout" class="w100">
								<el-option label="Default" value="" />
							</el-select>
						</el-form-item>
						<el-form-item label="Max page depth">
							<el-input-number v-model="form.maxDepth"></el-input-number>
						</el-form-item>
						<el-form-item label="Description">
							<el-input v-model="form.options.description" type="textarea" />
						</el-form-item>
					</el-col>
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
			dialogFormVisible: false,
			loading: true,

			form: {
				title: '',
				url: '',
				parent: 0,
				layout: '',
				options: {
					description: '',
					maxDepth: 3
				}
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
			this.sites.push({
				title: this.form.title,
				url: this.form.url,
				id: this.sites.length
			});

			this.dialogFormVisible = false;

			this.form = {
				title: '',
				url: '',
				parent: 0,
				layout: '',
				options: {
					description: '',
				}
			};
		},

		fetchData() {
			this.$api
				.get('sites?include=homepage.draft')
				.then((response) => {
					this.sites = response.data.data;
					this.loading = false;
				});
		}

	}
};
</script>