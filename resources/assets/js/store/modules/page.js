import { Definition } from 'classes/helpers';
import api from '../../plugins/http/api';

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

	updateFieldValue(state, { name, value, index }) {
		let idx = typeof index !== 'undefined' ? index : state.currentBlockIndex;
		const block = state.pageData.regions[state.currentRegion][idx];
		block.fields[name] = value;
		state.pageData.regions[state.currentRegion].splice(idx, 1, block);
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

		api
			.get(`page/${id}?include=blocks`)
			.then((response) => {
				commit('setPage', tempTransform(response.data.data));
			});
	}
};

const getters = {

	getFieldValue: (state) => (index, name) => {
		return state.pageData.blocks[state.currentRegion][index].fields[name];
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
