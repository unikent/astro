import _ from 'lodash';
import { Definition } from 'classes/helpers';
import api from 'plugins/http/api';
import { undoStackInstance } from 'plugins/undo-redo';

const state = {
	currentBlockIndex: null,
	currentRegion: 'main',
	blockMeta: {
		sizes: [],
		offsets: [],
		moved: null
	},
	pageData: {
		blocks: {
			main: []
		}
	},
	pageName: '',
	pageScale: .4,
	loaded: false
};

const mutations = {

	setPage(state, page) {
		state.loaded = true;

		if(!page.blocks) {
			page.blocks = {
				main: []
			};
		}

		state.pageData = page;
	},

	setBlock(state, { index } = { index: null }) {
		state.currentBlockIndex = index;
	},

	reorderBlocks(state, { from, to, value }) {
		state.pageData.blocks[state.currentRegion].splice(from, 1);
		state.pageData.blocks[state.currentRegion].splice(to, 0, value);
	},

	updateBlockPositionsOrder(state, { type, from, to, value }) {
		state.blockMeta[type].splice(from, 1);
		state.blockMeta[type].splice(to, 0, value);
	},

	updateBlockPositions(state, { type, index, value }) {
		state.blockMeta[type].splice(index, 1, value);
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
			const clone = _.clone(fields);
			_.set(clone, name, value);
			fields = clone;
		}
	},

	changePage(state, name) {
		state.pageName = name;
	},

	addBlock(state, { region, index, block }) {
		if(region === void 0) {
			region = state.currentRegion;
		}

		if(index === void 0) {
			index = state.pageData.blocks[state.currentRegion].length;
		}

		Definition.fillBlockFields(block);

		state.pageData.blocks[state.currentRegion].splice(index, 1, block || {});
	},

	deleteBlock(state, { index } = { index: null }) {
		if(index === null) {
			index = state.currentBlockIndex;
		}

		state.pageData.blocks[state.currentRegion].splice(index, 1);
	}
};

const actions = {

	fetchPage({ commit }, id) {

		// TODO: refactor into smaller methods
		api
			.get(`page/${id}?include=blocks`)
			.then(response => {
				const page = response.data.data;

				api
					.get(`layout/${page.layout_name}/definition?include=region_definitions.block_definitions`)
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
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};
