<template>
<el-card v-if="userCanSwitchClearingSites">
	<div slot="header" class="manage-table__header">
		<div class="main-header">
			Primary Clearing Site -
			<strong v-if="liveClearingSiteID">{{ clearingSites[liveClearingSiteID].name }}</strong>
			<span v-else>?</span>
		</div>
	</div>

	<div v-if="siteDataError">
		<el-alert
				title="Unable to read site data"
				type="error"
				description="An error occurred when retrieving clearing site data. Please refresh the page or contact the administrators."
				:closable="false"
				show-icon
		>
		</el-alert>
	</div>
	<div v-else class="columns">

		<div class="column">
			<el-select v-model="selectedSiteID" placeholder="Select Main Clearing Site" :no-data-text="(siteDataError ? 'Data Error' : 'Loading...')">
				<template v-if="siteDataReady">
				<el-option
						v-for="id in alternativeSites"
						:key="id"
						:label="clearingSites[id].name"
						:value="id">
				</el-option>
				</template>

			</el-select>
			<el-button :disabled="!siteDataReady || !selectedSiteID || (selectedSiteID === liveClearingSiteID)" title="Switch Main Clearing Site" @click="showConfirmSwitch = true">Switch Main Clearing Site</el-button>
		</div>

	</div>

	<el-dialog title="Switch Site" :visible.sync="showConfirmSwitch">

		<el-alert
			title="Are you sure?"
			type="warning"
			:closable="false"
			show-icon
		>
			<div>This will make <strong>{{ clearingSites[selectedSiteID].name }}</strong> the live clearing site.</div>
		</el-alert>
		<span slot="footer" class="dialog-footer">
			<el-button @click="showConfirmSwitch = false">Cancel</el-button>
			<el-button type="primary" @click="changeClearingSite">Confirm</el-button>
 		</span>
	</el-dialog>

</el-card>
	<div v-else>
		<el-alert
				title="You cannot switch clearing sites"
				type="error"
				description="You do not have permission to switch the live clearing site. Please contact the administrators."
				:closable="false"
				show-icon
		>
		</el-alert>
	</div>
</template>

<script>
import Config from 'classes/Config';
import { mapGetters } from 'vuex';

export default {

	data() {
		// get the configuration of which sites are available for clearing
		// with some defaults just for testing which should be removed before
		// going live / merging...
		const clearingConfig = Config.get('clearing', {
			'sites': [
				1,69,3
			],
			'live_host': 'www.kent.ac.uk',
		});
		let clearingSites = {};
		// initialise the object which maps available clearing sites by id to their data
		clearingConfig.sites.forEach((id) => {
			clearingSites[id] = null;
		});
		return {
			clearingConfig: clearingConfig,
			clearingSites: clearingSites,
			siteDataError: false,
			showConfirmSwitch: false,
			selectedSiteID: null,
		};
	},

	created() {
		this.fetchClearingSites();
	},

	computed: {
		...mapGetters([
			'getGlobalRole'
		]),
		/**
		 * Get the ids of the alternative clearing sites, excluding the current live one
		 * @return Array<Integer>
		 */
		alternativeSites() { return this.clearingConfig.sites;
			let alts = [];
			this.clearingConfig.sites.forEach((id) => {
				if(id !== this.liveClearingSiteID) {
					alts.push(id);
				}
			});
			return alts;
		},
		/**
		 * Get the ID of the current live clearing site or null if this is unknown.
		 * The current live site is considered to be the one whose host / domain is the same as the one set
		 * in the clearing config.
		 * @return integer|null
		 */
		liveClearingSiteID() {
			let liveID = null;
			if(this.siteDataReady) {
				this.clearingConfig.sites.forEach((id) => {
					if (this.clearingSites[id].host === this.clearingConfig.live_host) {
						liveID = id;
					}
				})
			}
			return liveID;
		},
		/**
		 * Has the site data been loaded for all the sites available to be the clearing site?
		 * @returns {boolean}
		 */
		siteDataReady() {
			let ready = true;
			this.clearingConfig.sites.forEach((id) => {
				if(!this.clearingSites[id]) {
					ready = false;
				}
			})
			return ready;
		},
		userCanSwitchClearingSites() {
			return 'admin' === this.getGlobalRole;
		}
	},

	methods: {
		changeClearingSite() {
			this.dialogVisible = false;
		},
		fetchClearingSites() {
			this.clearingConfig.sites.forEach((id) => {
				this.clearingSites[id] = null;
				this.$api
					.get(`sites/${id}`)
					.then(({ data: json }) => {
						this.clearingSites[id] = json.data;
						// if no selected site, and we have found the live site, set the selectedSiteID to it
						if(!this.selectedSiteID && this.liveClearingSiteID) {
							this.selectedSiteID = this.liveClearingSiteID;
						}
					})
					.catch(() => {
						this.siteDataError = true;
						// this.$message({
						// 	title: 'Error fetching clearing site information',
						// 	message: 'Unable to to fetch clearing site information at this time.',
						// 	type: 'error'
						// });
					});
			});
		},
	}
};
</script>