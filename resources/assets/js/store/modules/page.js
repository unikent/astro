import _ from 'lodash';
import Vue from 'vue';
import { Definition } from 'classes/helpers';
import api from 'plugins/http/api';
import { eventBus } from 'plugins/eventbus';
import Config from 'classes/Config';

const vue = new Vue();

/**
 * Store module containing current page related data.
 * @namespace state/page
 * @property {string} currentLayout - The name of the layout in use for the current page.
 * @property {int} currentLayoutVersion - The version number of the layout in use for the current page.
 * @property {number|null} currentBlockIndex - The index of the currently selected block in the currently selected region.
 * @property {string} currentRegion - The name of the region containing the currently selected block.
 * @property {Object} blockMeta - Some meta about blocks???
 * @property {Object} blockMeta.blocks - Object with keys as region names and values as Arrays of blocks.
 * @property {Array} invalidBlocks - array of block ids of invalid blocks within the page
 * @property {boolean} loaded - has the page data been successfully loaded or not?
 */
const state = {
	currentLayout: null,
	currentLayoutVersion: 1,
	currentBlockIndex: null,
	currentRegion: 'main',
	blockMeta: {
		blocks: {
			main: []
		}
	},
	pageData: {
		blocks: {
			main: []
		}
	},
	scale: .4,
	loaded: false,
	dragging: false,
	currentSavedState: '',
	invalidBlocks: []
};

const mutations = {
	/**
	 * Mutation to set the current page.
	 * @method
	 * @param {object} page Page object representing the current page.
	 * @memberof state/page#
	 */
	setPage(state, page) {
		if(!page){
			page = {};
		}
		if(!page.blocks) {
			page.blocks = {
				main: []
			};
		}

		if(page.layout) {
			state.currentLayout = page.layout.name;
			state.currentLayoutVersion = page.layout.version;
		}
		state.pageData = page;
	},


	setLoaded(state, loaded = true) {
		state.loaded = loaded;
	},

	setDragging(state, isDragging) {
		state.dragging = isDragging;
	},

	setScale(state, scale) {
		state.scale = scale;
	},

	setBlock(state, { index } = { index: null }) {
		state.currentBlockIndex = index;
	},

	reorderBlocks(state, { from, to, value }) {
		state.pageData.blocks[state.currentRegion].splice(from, 1);
		state.pageData.blocks[state.currentRegion].splice(to, 0, value);

		// update metadata order
		const val = state.blockMeta.blocks[state.currentRegion].splice(from, 1);
		state.blockMeta.blocks[state.currentRegion].splice(to, 0, val[0]);
	},

	updateBlockMeta(state, { type, region, index, value }) {
		let blockData = state.blockMeta.blocks[region];
		blockData.splice(index, 1, { ...blockData[index], [type]: value })
	},

	updateFieldValue(state, { index, name, value }) {
		let
			idx = index !== void 0 ? index : state.currentBlockIndex,
			fields = state.pageData.blocks[state.currentRegion][idx].fields;

		// if field exists just update it
		if(_.has(fields, name)) {
			_.set(fields, name, value);
		}
		// otherwise update all fields to maintain reactivity
		else {
			const clone = { ...fields };
			_.set(clone, name, value);
			fields = clone;
		}
	},

	updateBlockMedia(state, { index, value }) {
		let
			idx = index !== void 0 ? index : state.currentBlockIndex,
			blockData = state.pageData.blocks[state.currentRegion];

		if(!blockData[idx].media) {
			blockData[idx].media = [value];
		}
		else {
			blockData[idx].media.push(value);
		}

		blockData.splice(idx, 1, { ...blockData[idx] })
	},

	addBlock(state, { region, index, block }) {
		if(region === void 0) {
			region = state.currentRegion;
		}

		if(state.pageData.blocks[region] === void 0) {
			state.blockMeta.blocks = { ... state.blockMeta.blocks, [region]: [] };
			state.pageData.blocks = { ... state.pageData.blocks, [region]: [] };
		}

		if(index === void 0) {
			index = state.pageData.blocks[region].length;
		}

		if(block) {
			Definition.fillBlockFields(block);
		}

		state.blockMeta.blocks[region].splice(index, 0, {
			size: 0,
			offset: 0,
			dragging: false
		});
		state.pageData.blocks[region].splice(index, 0, block || {});
	},

	deleteBlock(state,  { region, index } = { region: 'main', index: null }) {
		if(region === null) {
			region = state.currentRegion;
		}

		if(index === null) {
			index = state.currentBlockIndex;
		}

		state.pageData.blocks[region].splice(index, 1);
		state.blockMeta.blocks[region].splice(index, 1);

		// TODO: use state for this
		Vue.nextTick(() => eventBus.$emit('block:updateOverlay', index));
	},

	updateCurrentSavedState(state) {
		state.currentSavedState = JSON.stringify(state.pageData.blocks);
	},

	resetCurrentSavedState(state) {
		state.currentSavedState = '';
	},

	addBlockValidationIssue(state, block_id) {
		if (state.invalidBlocks.indexOf(block_id) === -1) {
			state.invalidBlocks.push(block_id);
		}
	},

	deleteBlockValidationIssue(state, block_id) {
		const location = state.invalidBlocks.indexOf(block_id);
		if (location !== -1) {
			state.invalidBlocks.splice(location, 1);
		}
	},

	clearBlockValidationIssues() {
		state.invalidBlocks = [];
	},

	setPageTitle(state, { id, title }) {
		if(state.pageData && state.pageData.id == id) {
			state.pageData.title = title;
		}
	},

	setPageSlugAndPath(state, { id, slug }) {
		if(state.pageData && state.pageData.id == id) {
			state.pageData.slug = slug;
			state.pageData.path = state.pageData.path.substr(
				0,
				state.pageData.path.lastIndexOf(state.pageData.slug)
			) + slug
		}
	},

	updatePageStatus(state, { id, status }) {
		if(state.pageData && state.pageData.id == id) {
			state.pageData.status = status;
		}
	}

};

const actions = {

	fetchPage({ state, commit }, id) {
		commit('setPage', null);
		// TODO: refactor into smaller methods
		api
			.get(`pages/${id}?include=blocks.media,site`)
			.then(response => {
				const page = response.data.data;

				api
					.get(`layouts/${page.layout.name}/definition?include=region_definitions.block_definitions`)
					.then(({ data: region }) => {

						region.data.region_definitions.forEach((region) => {
							region.block_definitions.forEach(definition => {
								Definition.set(definition);
							});
						});

						commit('setBlockDefinitions', Definition.definitions, { root: true });

						let blocks;

						if(page.blocks) {
							blocks = page.blocks;
							delete page.blocks;
						}
						else {
							blocks = {};
						}

						commit('setPage', _.cloneDeep(page));
						commit('clearBlockValidationIssues');

						Object.keys(blocks).forEach(region => {
							blocks[region].forEach((block, index) => {
								commit('addBlock', { region, index, block })
							});
						});

						Object.keys(blocks).forEach(region => {
							blocks[region].forEach((block) => {
								if (typeof block.errors !== 'undefined' && block.errors !== null) {
									commit('addBlockValidationIssue', block.id);
								}
							});
						});
						// TODO: populate validations issues with those received from the api
						commit('setLoaded');
					});

			});
	},

	/**
	 * Saves a page
	 * @param {Object} input
	 * @param {Object} input.state - the context of the action - added by VueX
	 * @param {Object} input.commit - added by VueX
	 * @param {boolean} notify - show a notification?
	 * @return {promise} - api - to allow other methods to wait for the save
	 * to complete
	 * @memberof state/page#
	 */
	handleSavePage({ state, commit }, notify) {
		const blocks = state.pageData.blocks;
		const id = state.pageData.id;
		return api
			.put(`pages/${id}/content`, {
				blocks: blocks
			})
			/**
			successful save
			- only display the notification message if that's what we want (there's a defined payload)
			- eg on preview we don't want to show a save message
			*/
			.then(response => {
				if (notify) {
					// there are validation errors
					if (response.data.data.valid===0) {
						// create the message markup

						const message = vue.$createElement(
							'div',
							{
								'style': {
									color: '#bb9132'
								},
							},
							[
								vue.$createElement('p', 'The page saved ok, but there are some validation errors.'),
								vue.$createElement('p', 'You won\'t be able to publish till these are fixed.'),
								vue.$createElement('a', {
									attrs: {
										href: '#'
									},
									on: {
										click(e) {
											eventBus.$emit('sidebar:openErrors', e);
										}
									}
								}, 'Check the error sidebar for details.')
							],

						);
						vue.$notify({
							title: 'Saved',
							message: message,
							type: 'warning',
							duration: 10000,
							onClick() {
								this.close();
							}
						});
					}
					// we're all good
					else if (response.data.data.valid===1) {
						vue.$notify({
							title: 'Saved',
							message: 'You saved this page successfully.',
							type: 'success',
							duration: 4000
						});
					}
				}
				commit('updateCurrentSavedState');
			})
			/**
			unsuccessful save, such as a network problem
			*/
			.catch(() => {
				vue.$notify({
					title: 'Not saved',
					message: 'There was a problem and this page has not been saved. Please try again later.',
					type: 'error',
					duration: 0
				});
			});
	},

	setPageStatus({ commit, rootGetters }, { id, arrayPath, status }) {
		if(!id) {
			id = rootGetters['site/getPage']({ arrayPath }).id;
		}

		commit('updatePageStatus', { id, status });
	}
};

const getters = {

	/**
	 * Getter to determine published state of the current page.
	 * A Page can be either:
	 * - new - Never been published.
	 * - draft - Changed since last published.
	 * - published - Not modified since last published.
	 * @param state
	 * @returns {string} - The state as a string.
	 * @memberof state/page#
	 * @todo - implement once supported by the API.
	 */
	publishStatus: (state) =>  {
		return (state.loaded ? state.pageData.status : '');
	},

	/**
	 * Getter to retrieve the title of the current page, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page title, or null if there is no current page.
	 * @memberof state/page#
	 */
	pageTitle: (state) => {
		return state.loaded ? state.pageData.title : '';
	},

	/**
	 * Getter to retrieve the slug of the current page, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page slug, or null if there is no current page.
	 * @memberof state/page#
	 */
	pageSlug: (state) => {
		return state.loaded ? state.pageData.slug : '';
	},

	/**
	 * Getter to retrieve the path of the current page, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page's path, or null if there is no current page.
	 * @memberof state/page#
	 */
	pagePath: (state) => {
		return (state.loaded ? state.pageData.path : '');
	},

	siteTitle: (state) => {
		return (state.loaded ? state.pageData.site.title : '');
	},

	/**
	 * Getter to retrieve the root path of the current page's site, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page's site's path or null if there is no current page.
	 * @memberof state/page#
	 * @todo Site should be a separate object in store state.
	 */
	sitePath: (state) => {
		return (state.loaded ? state.pageData.site.path : '');
	},

	/**
	 * Getter to retrieve the domain name for the current page's site, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current domain name, or null if there is no current page.
	 * @memberof state/page#
	 * @todo Site should be a separate object in store state.
	 */
	siteDomain: (state) => {
		return (state.loaded ? state.pageData.site.host : '');
	},

	/**
	 * Get the data representing the current page.
	 * @param state
	 * @returns {state/page.pageData|{blocks}|pageData|Object|*}
	 */
	currentPage: (state) => {
		return state.pageData;
	},

	/**
	 * Get the URL at which the current page can be previewed in the editor.
	 * @param state
	 * @param getters
	 * @returns {string} Full URL
	 */
	draftPreviewURL: (state, getters) => {
		return `${Config.get('base_url', '')}` + '/draft/' + getters.siteDomain + getters.sitePath + (getters.pagePath == '/' ? '' : getters.pagePath);
	},

	/**
	 * Get the URL at which the published version of the current page can be previewed in the editor.
	 * @param state
	 * @param getters
	 * @returns {string} Full URL
	 */
	publishedPreviewURL: (state, getters) => {
		return `${Config.get('base_url', '')}` + '/published/' + getters.siteDomain + getters.sitePath + (getters.pagePath == '/' ? '' : getters.pagePath);
	},

	getFieldValue: (state) => (index, name) => {
		const block = state.pageData.blocks[state.currentRegion][index];

		if(!block) {
			return null;
		}

		return _.get(block.fields, name, null);
	},

	getCurrentFieldValue: (state, getters) => (name) => {
		return getters.getFieldValue(state.currentBlockIndex, name);
	},

	getCurrentBlock: (state) => () => {
		return state.pageData.blocks[state.currentRegion][state.currentBlockIndex];
	},

	getBlockMeta: (state) => (index, region, prop = false) => {
		const blockMeta = state.blockMeta.blocks[region][index];
		return prop ? blockMeta[prop] : blockMeta;
	},

	getInvalidBlocks: (state) => () => {
		return state.invalidBlocks;
	},

	getCurrentRegion: (state) => () => {
		return state.currentRegion;
	},

	getBlocks: (state) => () => {
		return state.pageData.blocks;
	},

	scaleDown: (state) => () => {
		return state.scale;
	},

	scaleUp: (state) => () => {
		// return scale with three digits after decimal point
		return state.scale !== 0 ? Math.round(1 / state.scale * 1000) / 1000 : 1;
	},

	unsavedChangesExist: (state) => () => {
		if(state.currentSavedState.length === 0) {
			// if user has not edited a page yet so we do not have any unsaved changes
			return false;
		}
		else {
			return state.currentSavedState != JSON.stringify(state.pageData.blocks);
		}
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};
