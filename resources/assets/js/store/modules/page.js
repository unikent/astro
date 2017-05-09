import api from '../../plugins/http/api';

const state = {
	currentBlockIndex: null,
	currentRegion: 'main',
	blockMeta: {
		sizes: [],
		offsets: [],
		moved: null
	},
	pageData: {},
	pageName: '',
	pageScale: .4
};

const mutations = {

	setPage(state, page) {
		state.pageData = page;
	},

	setBlock(state, { index } = { index: null }) {
		state.currentBlockIndex = index;
	},

	reorderBlocks(state, { from, to, value }) {
		state.pageData.regions[state.currentRegion].splice(from, 1);
		state.pageData.regions[state.currentRegion].splice(to, 0, value);
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

	// addBlock(state, { index, row }) {},
	// deleteBlock(state, { index, row }) {}
};

const actions = {

	fetchPage({ commit }, id) {
		const tempTransform = (data) => {
			if(data.blocks) {
				data.blocks.forEach((block) => {

					if(data.regions === void 0) {
						data.regions = {};
					}

					if(data.regions[block.region_name] === void 0) {
						data.regions[block.region_name] = [];
					}

					data.regions[block.region_name].push(block);
				});

				delete data.blocks;
			}

			return data;
		};

		api
			.get(`page/${id}?include=blocks`)
			.then((response) => {
				commit('setPage', tempTransform(response.data.data));
			});
	}
};

const getters = {

	getFieldValue: (state) => (index, name) => {
		return state.pageData.regions[state.currentRegion][index].fields[name];
	},

	getCurrentFieldValue: (state, getters) => (name) => {
		return getters.getFieldValue(state.currentBlockIndex, name);
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};
