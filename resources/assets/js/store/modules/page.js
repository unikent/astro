import _ from 'lodash';
import Vue from 'vue';
import { Definition } from 'classes/helpers';
import api from 'plugins/http/api';
import { undoStackInstance } from 'plugins/undo-redo';
import { eventBus } from 'plugins/eventbus';

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
	pageName: '',
	scale: .4,
	loaded: false,
	dragging: false
};

const mutations = {

	setPage(state, page) {
		if(!page.blocks) {
			page.blocks = {
				main: []
			};
		}

		state.currentLayout = page.layout_name;
		state.currentLayoutVersion = page.layout_version;

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

		// TODO: use state for this
		Vue.nextTick(() => eventBus.$emit('block:updateOverlay'));
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

	changePage(state, name) {
		// TODO: replace this with actual domain when that info is available
		state.pageName = `kent.ac.uk/site-name${name}`;
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
					.get(`layouts/${page.layout_name}/definition?include=region_definitions.block_definitions`)
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

						Object.keys(blocks).forEach(region => {
							blocks[region].forEach((block, index) => {
								commit('addBlock', { region, index, block })
							});
						});

						commit('setLoaded');

						undoStackInstance.init(state.pageData);
					});

			});
	}
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
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};
