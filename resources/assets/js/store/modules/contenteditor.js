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

	currentSectionIndex: null,
	currentSection: null,
	currentDefinition: null,
	currentBlock: null,
	currentRegion: null
};

const getters = {
	figureOutCurrentDefinition(state,getters,rootState){ console.log('ow');
		return getters.currentBlock ? rootState.definitions.blockDefinitions[getters.currentBlock.definition_name + '-v' + getters.currentBlock.definition_version] : null;
	},

	/**
	 * Get the currently selected block.
	 * @param state
	 * @param getters
	 * @returns {Object|null}
	 */
	figureOutCurrentBlock(state,getters){
		const section = getters.currentSection;
		console.log('hi');
		return section ? section.blocks[state.currentBlockIndex] : null;
	},

	/**
	 * Get the region containing the currently selected block.
	 * @param state
	 * @returns {Array|null}
 	*/
//	currentRegion(state,getters,rootState){
//		return rootState.page.pageData.blocks[state.currentRegionName];
//	},

	/**
	 * Get the section object containing the currently selected block.
	 * @param state
	 * @param getters
	 * @returns {Object|null}
	 */
//	currentSection(state,getters){
//		return (getters.currentSectionIndex != -1) ? getters.currentRegion[getters.currentSectionIndex] : null;
//	},

	/**
	 * Get the index in the current region of the section containing the currently selected block.
	 * @param state
	 * @returns {number} The index of the current named section, or -1.
	 */
//	currentSectionIndex(state,getters){
//		const region = getters.currentRegion;
//		console.log('argj');//state);

//		return region ? region.findIndex(el => el.section_name === state.currentSectionName) : -1;
//	}
};

const mutations = {
	/**
	 * Sets the values that are used to represent the current block.
	 * @param state
	 * @param {string} regionName
	 * @param {string} sectionName
	 * @param {number} blockIndex
	 */
	setCurrentBlock( state, {regionName, sectionName, blockIndex, allBlocks, definitions} ) {
		state.currentBlockIndex = blockIndex;
		state.currentRegionName = regionName;
		state.currentRegion = allBlocks[regionName];
		state.currentSectionName = sectionName;
		state.currentSectionIndex = state.currentRegion ? state.currentRegion.findIndex(el => el.name === state.currentSectionName) : -1;
		state.currentSection = state.currentRegion ? state.currentRegion[state.currentSectionIndex] : null;
		state.currentBlock = state.currentSection ? state.currentSection.blocks[state.currentBlockIndex] : null;
		const type = state.currentBlock.definition_name + '-v' + state.currentBlock.definition_version;
		state.currentDefinition = state.currentBlock ? definitions[type] : null;
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
			arg['allBlocks'] = rootState.page.pageData.blocks;
			arg['definitions'] = rootState.definition.blockDefinitions;
			commit('setCurrentBlock', arg);
			commit('collapseSidebar');
		}
		// make sure we get to see the block menu if we're currently seeing the pages or other sidebar and a user clicks on any block
		if(rootState.menu.active!=='blocks') {
			commit('updateMenuActive', 'blocks');
		}
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};