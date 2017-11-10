import _ from 'lodash';

/**
 * Represents the state of the page content (blocks) editor and provides actions, getters and mutations for components
 * to interact with it.
 */

const state = {
	/**
	 * @type {string} The name of the region containing the currently selected block.
	 */
	currentRegionName: 'main',
	/**
	 * @type {string} The name of the section containing the currently selected block.
	 */
	currentSectionName: 'catch-all',
	/**
	 * @type {number} The index of the currently selected block in its section.
	 */
	currentBlockIndex: null,
};

const getters = {

	/**
	 * Get the definition for the currently selected block.
	 * @param state
	 * @param getters
	 * @param rootState
	 * @returns {*}
	 */
	currentDefinition(state,getters,rootState){
		const block = getters.currentBlock;
		if(block) {
			const type = block.definition_name + '-v' + block.definition_version;
			return rootState.definition.blockDefinitions[type];
		}
		return null;
	},

	/**
	 * Get the currently selected block.
	 * @param state
	 * @param getters
	 * @returns {Object|null}
	 */
	currentBlock(state,getters){
		return getters.currentSection ? getters.currentSection.blocks[state.currentBlockIndex] : null;
	},

	/**
	 * Get the region containing the currently selected block.
	 * @param state
	 * @returns {Array|null}
 	*/
	currentRegion(state,getters,rootState){
		return rootState.page.pageData.blocks[state.currentRegionName];
	},

	/**
	 * Get the section object containing the currently selected block.
	 * @param state
	 * @param getters
	 * @returns {Object|null}
	 */
	currentSection(state,getters){
		return getters.currentRegion ? getters.currentRegion[getters.currentSectionIndex] : null;
	},

	/**
	 * Get the index in the current region of the section containing the currently selected block.
	 * @param state
	 * @returns {number} The index of the current named section, or -1.
	 */
	currentSectionIndex(state,getters){
		const region = getters.currentRegion;
		const name = state.currentSectionName;
		return region ? region.findIndex(el => el.name === name) : -1;
	},
/*
	getFieldValue: (state, getters) => (index, name) => {
		const block = state.pageData.blocks[state.currentRegion][getters.currentSectionIndex].blocks[index];

		if(!block) {
			return null;
		}

		return _.get(block.fields, name, null);
	},
*/

	/**
	 * Get the value for the named field within the block currently selected in the editor.
	 * @param state
	 * @param getters
	 */
	getCurrentFieldValue: (state, getters) => (name) => {
		const block = getters.currentBlock;
		return _.get(block.fields, name, null);
	},

	/*
	getCurrentBlock: (state) => () => {
		return state.pageData.blocks[state.currentRegion][state.currentBlockIndex];
	},
*/
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

};

const mutations = {
	/**
	 * Sets the values that are used to represent the current block.
	 * @param state
	 * @param {string} regionName
	 * @param {string} sectionName
	 * @param {number} blockIndex
	 */
	setCurrentBlock( state, {regionName, sectionName, blockIndex} ) {
		state.currentBlockIndex = blockIndex;
		state.currentRegionName = regionName;
		state.currentSectionName = sectionName;
	},

	mutateFields( state, { fields, name, value} ) {
		// if field exists just update it
		if (_.has(fields, name)) {
			_.set(fields, name, value);
		}
		// otherwise update all fields to maintain reactivity
		else {
			const clone = {...fields};
			_.set(clone, name, value);
			fields = clone;
		}
	}
};

const actions = {
	/**
	 * Changes the currently selected block in the editor
	 * and shows the block options editor in the sidebar.
	 * @param commit
	 * @param state
	 * @param rootState
	 * @param {Object} arg
	 *    @param {string} regionName
	 *    @param {string} sectionName
	 *    @param {string} blockIndex
	 */
	changeBlock({ commit, state, rootState}, arg ) {
		// have we actually selected a different block?
		if(state.currentBlockIndex !== arg.blockIndex || state.currentRegionName !== arg.regionName || state.currentSectionName !== arg.sectionName) {
			commit('setCurrentBlock', arg);
			commit('collapseSidebar');
		}
		// make sure we get to see the block menu if we're currently seeing the pages or other sidebar and a user clicks on any block
		if(rootState.menu.active!=='blocks') {
			commit('updateMenuActive', 'blocks');
		}
	},

	updateFieldValue({ commit, state, getters}, { index, name, value }) {
		const currentSection = getters.currentSection;
		let
			idx = index !== void 0 ? index : state.currentBlockIndex;

			// rootState.page.pageData.blocks[state.currentRegionName][state.currentSectionIndex].blocks[idx].fields;

		commit('mutateFields', {
			fields: currentSection ? currentSection.blocks[idx].fields : null,
			name: name,
			value: value
		});
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};