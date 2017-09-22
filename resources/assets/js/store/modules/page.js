import _ from 'lodash';
import Vue from 'vue';
import { mapMutations } from 'vuex';
import { Definition } from 'classes/helpers';
import api from 'plugins/http/api';
import { undoStackInstance } from 'plugins/undo-redo';
import { eventBus } from 'plugins/eventbus';

/**
 * Page State Module
 * @namespace state/page
 * @property {string} currentLayout - The name of the layout in use for the current page.
 * @property {int} currentLayoutVersion - The version number of the layout in use for the current page.
 * @property {number|null} currentBlockIndex - The index of the currently selected block in the currently selected region.
 * @property {string} currentRegion - The name of the region containing the currently selected block.
 * @property {Object} blockMeta - Some meta about blocks???
 * @property {Object} blockMeta.blocks - Object with keys as region names and values as Arrays of blocks.
 * @property {Array} invalidBlocks - array of block ids of invalid blocks within the page
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
	invalidBlocks: [],
};

const mutations = {
    /**
	 * Mutation to set the current page.
	 * @method
     * @param {object} page Page object representing the current page.
	 * @memberof state/page#
     */
	setPage: function(state, page) {
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

	changePage(state, { title, path, slug }) {
		// TODO: replace this with actual domain when that info is available
		if(slug === null) {
			slug = '/';
		}
		// state.pageTitle = `${title}`;
		// state.pagePath = `kent.ac.uk/site-name${path}`;
		// state.pageSlug = `${slug}`;
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

	savePageMeta(state, page) {
		state.pageData.title = page.title;
	},

	addBlockValidationIssue(state, block_id) {
		if (state.invalidBlocks.indexOf(block_id) === -1) {
			state.invalidBlocks.push(block_id);
		}
	},

	deleteBlockValidationIssue(state, block_id) {
		const location = state.invalidBlocks.indexOf(block_id);
		if (location !== -1) {
			reducedItems = state.invalidBlocks.splice(location, 1);
			state.invalidBlocks = reducedItems;
		}
	},

	clearBlockValidationIssues() {
		state.invalidBlocks = [];
	}

};

const actions = {

	fetchPage({ state, commit }, id) {

		// TODO: refactor into smaller methods
		api
			.get(`pages/${id}?include=blocks.media`)
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
							blocks[region].forEach((block, index) => {
								if (block.errors !== null) {
									commit('addBlockValidationIssue', block.id);
								}
							});
						});

						commit('setLoaded');
						// @TODO - populate validations issues with those received from the api

						undoStackInstance.init(state.pageData);
					});

			});
	},

	/**
	 * Saves a page
	 * @param {Object} input
	 * @param {Object} input.state - the context of the action - added by VueX
	 * @param {Object} input.commit - added by VueX
	 * @param {Object} payload - parameter object
	 * @param {callback} payload.message - function to display a message
	 * @return {promise} - api - to allow other methods to wait for the save
	 * to complete
	 * @memberof state/page#
	 */
	handleSavePage({ state, commit }, payload) {
		const blocks = state.pageData.blocks;
		const id = state.pageData.id;
		return api
			.put(`pages/${id}/content`, {
				blocks: blocks
			})
			.then(() => {
				payload.message({
					message: 'Page saved',
					type: 'success',
					duration: 2000
				});
				commit('updateCurrentSavedState');
			})
			.catch(() => {});
	},
};

const getters = {

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

	scaleDown: (state) => () => {
		return state.scale;
	},

	scaleUp: (state) => () => {
		// return scale with three digits after decimal point
		return state.scale !== 0 ? Math.round(1 / state.scale * 1000) / 1000 : 1;
	},

	unsavedChangesExist: (state) => () => {
		if (state.currentSavedState.length === 0) {
			// if user has not edited a page yet so we do not have any unsaved changes
			return false;
		}
		else {
			return state.currentSavedState != JSON.stringify(state.pageData.blocks);
		}
	},
};


export default {
	state,
	mutations,
	actions,
	getters
};
