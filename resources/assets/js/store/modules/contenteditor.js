
const state = {
	currentRegionName: 'main',
	currentSectionName: 'catch-all',
	currentBlockIndex: null
};

const getters = {
	/**
	 * Get the currently selected block.
	 * @param state
	 * @param getters
	 * @returns {Object|null}
	 */
	currentBlock(state,getters){
		const section = getters.currentSection;
		return section ? section.blocks[state.currentBlockIndex] : null;
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
		return (getters.currentSectionIndex != -1) ? getters.currentRegion[getters.currentSectionIndex] : null;
	},

	/**
	 * Get the index in the current region of the section containing the currently selected block.
	 * @param state
	 * @returns {number} The index of the current named section, or -1.
	 */
	currentSectionIndex(state,getters){
		const region = getters.currentRegion;
		return region ? region.findIndex(el => el.section_name === state.currentSectionName) : -1;
	}
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
	}
};

const actions = {
	changeBlock({ commit, state, rootState}, arg ) {
		// have we actually selected a different block?
		if(state.currentBlockIndex !== arg.blockIndex || state.currentRegionName !== arg.regionName || state.currentSectionName !== arg.sectionName) {
			commit('setCurrentBlock', arg );
//			this.collapseSidebar();
		}
		// make sure we get to see the block menu if we're currently seeing the pages menu and a user clicks on any block
//		if(this.activeMenuItem!=='blocks') {
//			this.updateMenuActive('blocks');
//		}
	}
};

export default {
	state,
	mutations,
	actions,
	getters
};