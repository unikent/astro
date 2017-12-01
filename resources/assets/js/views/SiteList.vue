/**
 * List of Sites
 *
 * This provides a list of sites which are avaliable to the logged in user
 *
 * Note
 * ----
 * Permission checking - we're checking on two admin only permissions which are not actually defined for the Actions buttons.  * These permissions are
 * 1. site.create
 * 2. site.delete
 * This shouldn't matter, since admin is a 'let them do anything' switch,  but if we need to add them we should make the names * consistent.
 */
<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Manage sites</span>
		<el-button v-if="canUser('site.create')" type="default" @click="dialogFormVisible = true" class="manage-table__add-button">
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
					v-for="site in sitesWithRoles"
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
							<router-link :to="`/site/${site.id}/menu`" v-if="canUserOnSite('site.options.edit', site.currentRole)">
								<el-button type="default" size="small">
									Menu
								</el-button>
							</router-link>
							<router-link :to="`/site/${site.id}/users`" v-if="canUserOnSite('permissions.site.assign', site.currentRole)">
								<el-button type="default" size="small">
									Manage users
								</el-button>
							</router-link>
							<!-- <el-button @click="askRemove(site.id)" type="default" size="small" v-if="canUserOnSite('site.delete', site.currentRole)">
								<icon name="delete" width="14" height="14" />
							</el-button> -->
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<el-dialog title="Add Site" :visible.sync="dialogFormVisible">
			<el-form :model="form" label-position="top">
				<el-row type="flex" :gutter="20">

					<el-col :span="11">
						<el-form-item label="Name">
							<el-input v-model="form.name" auto-complete="off"></el-input>
						</el-form-item>
					</el-col>

					<el-col :span="11" :offset="2">
						<el-form-item label="Site Template">
							<el-select v-model="form.siteDefinitionId" class="w100" placeholder="Select">
								<el-option
									v-for="(siteDefinition, siteID) in siteDefinitions"
									:label="siteDefinition.label + ' (v' + siteDefinition.version + ')'"
									:value="siteID"
									:key="siteID"
								>
								</el-option>
							</el-select>
						</el-form-item>
					</el-col>

				</el-row>

				<el-row type="flex" :gutter="20">

					<el-col :span="11">

						<el-form-item label="Host">
							<el-input
								v-model="form.host"
								auto-complete="off"
								placeholder="www.kent.ac.uk"
							/>
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
				<el-button type="primary" @click="addSite" :disabled="disableSubmit">Add Site</el-button>
			</span>
		</el-dialog>
	</div>
</el-card>
</template>

<script>
import Icon from 'components/Icon';
import { mapGetters, mapState } from 'vuex';
import permissions  from 'store/modules/permissions'; // this is to use canUser directly and provide our own state for each site in the list
import Config from 'classes/Config';

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
				name: '',
				path: '',
				host: '',
				siteDefinitionId: '',
				errors: '',
			}
		};
	},

	created() {
		this.fetchData();
	},

	computed: {

		...mapState({
			siteDefinitions: state => state.site.siteDefinitions
		}),

		...mapGetters([
			'canUser',
			'getPermissions',
			'getGlobalRole'
		]),

		disableSubmit() {
			return this.form.siteDefinitionId === ''	 ||
					this.form.name === '' ||
					this.form.host === '';
		},

		sitesWithRoles() {
			let sitesWithRoles = [];

			for (var i = 0, len = this.sites.length; i < len; i++) {
				// if the site has a role for the user then add the currentRole to the list of sites
				let currentSite = this.sites[i];
				currentSite['currentRole'] = ''; // set a default
				if (currentSite.users) {
					let result = currentSite.users.find((element) => element.username === Config.get('username'));
					if (result) {
						currentSite['currentRole'] = result.role;
					}
				}

				sitesWithRoles.push(currentSite);
			}
			return sitesWithRoles;
		}
	},

	methods: {

		/**
		 * a wrapper around canUser which provides a fake state to make it work in the context
		 * of sites which are not loaded into the vuex state
		 *
		 * @param {string} permissionSlug - the slug of the permission ie. page.publish
		 * @param {string} siteRole - the role the user has on the site ie. site.owner
		 */
		canUserOnSite(permissionSlug, siteRole) {
			let siteState = {};
			siteState.currentRole = siteRole;
			siteState.permissions = this.getPermissions;
			siteState.globalRole = this.getGlobalRole;

			return permissions.getters.canUser(siteState)(permissionSlug);
		},

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
			this.resetForm();
		},

		resetForm() {
			this.form = {
				name: '',
				host: '',
				path: '',
				errors: '',
				siteDefinitionId: ''
			};
			this.loading = false;
		},

		addSite() {
			this.loading = true;
			this.dialogFormVisible = false;

			// paths needs to be blank or a srting starting with a /
			if (this.form.path.length !== 0) {
				if (this.form.path[0] !== '/') {
					this.form.path = '/' + this.form.path;
				}
			}

			let site = {
				name: this.form.name,
				host: this.form.host,
				path: this.form.path,
				site_definition: {
					name: this.siteDefinitions[this.form.siteDefinitionId].name,
					version: this.siteDefinitions[this.form.siteDefinitionId].version
				}
			};

			this.form.errors = [];
			this.$api
				.post('sites', site)
				.then(() => {
					// success, so let's refresh what data as have from the api
					this.fetchData();

					// reset the form
					this.resetForm();

				})
				.catch((errors) => {
					this.dialogFormVisible = true;
					this.form.errorMsgs = errors.response.data.errors[0].message;
					this.form.errorsDetails = errors.response.data.errors[0].details;
					this.loading = false;
				});
		},

		fetchData() {
			// make sure we get all the data back before continuing
			this.$api
				.get('sites?include=homepage.revision,users')
				.then(({ data: json }) => {

					this.sites = json.data;

					// now we have all the data unhide the list
					this.loading = false;
				})
				.catch(() => {
					// TODO: what do we do when the API is unavaliable
				});
		}
	}
};
</script>
