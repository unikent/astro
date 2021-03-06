import _ from 'lodash';
import Vue from 'vue';
import { getPublishedPreviewURL, getDraftPreviewURL, Definition } from 'classes/helpers';
import api from 'plugins/http/api';
import { eventBus } from 'plugins/eventbus';
import { undoStackInstance } from 'plugins/undo-redo';
import { isIframe } from 'classes/helpers';
import Validation from 'classes/Validation';

const vue = new Vue();

/**
 * Store module containing current page related data.
 * @namespace state/page
 * @property {string} currentLayout - The name of the layout in use for the current page.
 * @property {int} currentLayoutVersion - The version number of the layout in use for the current page.
 * @property {number|null} currentBlockIndex - The index of the currently selected block in the currently selected region.
 * @property {string} currentRegion - The name of the region containing the currently selected block.
 * @property {Array} invalidBlocks - array of block ids of invalid blocks within the page
 * @property {boolean} loaded - has the page data been successfully loaded or not?
 */
const state = {
	currentLayout: null,
	currentLayoutVersion: 1,
	layoutErrors: [],
	currentBlockIndex: null,
	currentRegion: null,
	pageData: {
		blocks: {}
	},
	loaded: false,
	currentSavedState: '',
	invalidBlocks: [],
	currentPageArrayPath: null,
    currentLayoutLabel: null,
};

const mutations = {
	/**
	 * Mutation to set the current page.
	 * @method
	 * @param {object} page Page object representing the current page.
	 * @memberof state/page#
	 */
	setPage(state, page) {
		if(!page) {
			page = {};
		}
		if(!page.blocks) {
			page.blocks = {};
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

	setBlock(state, { index } = { index: null }) {
		state.currentBlockIndex = index;
	},

	setLayoutErrors(state, layoutErrors) {
		state.layoutErrors = layoutErrors;
	},

	reorderBlocks(state, { from, to, region, section }) {
		const block = state.pageData.blocks[region][section].blocks.splice(from, 1)[0];
		state.pageData.blocks[region][section].blocks.splice(to, 0, block);

		// TODO: use state for this
		runInIframeAfterTick(() => eventBus.$emit('block:updateBlockOverlays', to));
	},

	updateFieldValue(state, { index, name, value, region, section }) {
		let fields = state.pageData.blocks[region][section].blocks[index].fields;

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

	updateBlockMedia(state, { index, region, section, value }) {
		let blockData = state.pageData.blocks[region][section].blocks;

		if(!blockData[index].media) {
			blockData[index].media = [value];
		}
		else {
			blockData[index].media.push(value);
		}

		blockData.splice(index, 1, { ...blockData[index] })
	},

	/**
	 * Adds a block to a section in a region
	 *
	 * @param state
	 * @param {string} region - The name of the region to add the block to.
	 * @param {number} index - The position in the section to add the block, or null to add at end.
	 * @param {BlockData} block - The data representing the block to be added.
	 * @param {number} sectionIndex - The index of the section in the specified region.
	 * @param {string} sectionName - The name of the section to add the block to.
	 * @param {boolean} replace - Whether or not to replace the block
	 *
	 * If the specified region and / or section do not exist in the block data, they will be added.
	 */
	addBlock(state, { region, index, block, sectionIndex, sectionName, replace } ) {

		// if region is not yet defined in block data, add it
		if(state.pageData.blocks[region] === void 0) {
			state.pageData.blocks = { ... state.pageData.blocks, [region]: [] };
		}

		// if section is not yet defined in region data, add it
		// TODO refactor - is this necessary?  Should be more robust...
		if(state.pageData.blocks[region][sectionIndex] === void 0) {
			let sections = state.pageData.blocks[region];

			for(let i = sections.length; i <= sectionIndex; ++i) {
				sections.push({
					name: (i === sectionIndex ? sectionName : 'unknown-section'),
					blocks: []
				});
			}
		}

		if(index === void 0) {
			index = state.pageData.blocks[region][sectionIndex].blocks.length;
		}

		if(block) {
			Definition.fillFields(block);
		}

		state.pageData.blocks[region][sectionIndex].blocks.splice(index, (replace ? 1 : 0), block || {});

		if(replace) {
			runInIframeAfterTick(
				() => eventBus.$emit('block:showSelectedOverlay', {
					id: block.id
				})
			);
		}

		// TODO: use state for this
		runInIframeAfterTick(() => eventBus.$emit('block:updateBlockOverlays'));
	},

	initialiseBlock(state, { regionName, sectionIndex, blockIndex, block }) {
		Definition.fillFields(block);
		state.pageData.blocks[regionName][sectionIndex].blocks.splice(blockIndex, 1, block);
	},

	/**
	 * Delete the specified block from the page.
	 *
	 * @param state
	 * @param {string} region - The name of the region containing the block.
	 * @param {number} section - The index in the region of the section containing the block.
	 * @param {number} index - The index of the block in its section.
	 */
	deleteBlock(state,  { region, section, index }) {
		state.pageData.blocks[region][section].blocks.splice(index, 1);

		// TODO: use state for this
		runInIframeAfterTick(() => eventBus.$emit('block:updateBlockOverlays'));
	},

	updateCurrentSavedState(state) {
		state.currentSavedState = JSON.stringify(state.pageData.blocks);
	},

	resetCurrentSavedState(state) {
		state.currentSavedState = '';
	},

	addBlockValidationIssue(state, blockId) {
		if (state.invalidBlocks.indexOf(blockId) === -1) {
			state.invalidBlocks.push(blockId);
		}
	},

	deleteBlockValidationIssue(state, blockId) {
		const location = state.invalidBlocks.indexOf(blockId);
		if (location !== -1) {
			state.invalidBlocks.splice(location, 1);
		}
	},

	setPageTitle(state, { id, title }) {
		if(state.pageData && state.pageData.id === Number(id)) {
			state.pageData.title = title;
		}
	},

	setPagePath(state, { id, path }) {
		if(state.pageData && state.pageData.id === Number(id)) {
			state.pageData.path = path
		}
	},

	setPageSlugAndPath(state, { id, slug }) {
		if(state.pageData && state.pageData.id === Number(id)) {
			state.pageData.slug = slug;
			state.pageData.path = state.pageData.path.substr(
				0,
				state.pageData.path.lastIndexOf(state.pageData.slug)
			) + slug
		}
	},

	updatePageStatus(state, { id, status }) {
		if(state.pageData && state.pageData.id === Number(id)) {
			state.pageData.status = status;
		}
	},

	setCurrentPageArrayPath(state, arrayPath) {
		state.currentPageArrayPath = arrayPath;
	},

	setCurrentLayoutLabel(state, label) {
	    state.currentLayoutLabel = label;
    },
};

const actions = {

	fetchPage({ state, commit, dispatch }, id) {
		commit('setPage', null);
		return api
			.get(`pages/${id}?include=blocks.media,site,ancestors`)
			.then(response => {
				const page = response.data.data;

				return api
					.get(`layouts/${page.layout.name}-v${page.layout.version}/definition?include=region_definitions.block_definitions`)
					.then(({ data: region}) => {
						region.data.region_definitions.forEach(region => {
							Definition.addRegionDefinition(region);
							region.block_definitions.forEach(definition => {
								Definition.set(definition);
							});
						});
                        commit('setCurrentLayoutLabel', region.data.label);
						commit('setBlockDefinitions', Definition.definitions, { root: true });
						commit('setPage', _.cloneDeep(page));
						dispatch('initialiseBlocksAndValidate', state.pageData.blocks);
						undoStackInstance.init(state.pageData);
						commit('setLoaded');
					});

			});
	},

	initialiseBlocksAndValidate({ commit, dispatch }, blocks) {
		commit('clearBlockErrors');

		Object.keys(blocks).forEach(regionName => {
			blocks[regionName].forEach((section, sectionIndex) => {
				blocks[regionName][sectionIndex].blocks.forEach((block, blockIndex) => {
					commit('initialiseBlock', {
						regionName,
						sectionIndex,
						blockIndex,
						block
					});

					dispatch('addBlockErrors', {
						block,
						regionName,
						sectionName: section.name,
						sectionIndex,
						blockIndex
					});
				});
			});
		});
	},

	addBlockErrors(
		{ commit },
		{ block, regionName, sectionName, sectionIndex, blockIndex }
	) {
		const
			definitionType = `${block.definition_name}-v${block.definition_version}`,
			definition = Definition.get(definitionType),
			validator = Definition.getValidator(definition),
			fieldsWithValidation = Validation.flattenRules(
				Definition.getRules(definition)
			),
			fieldsWithErrors = [];

		if(fieldsWithValidation.length) {
			commit('addBlockErrors', {
				block,
				blockInfo: {
					regionName,
					sectionName,
					sectionIndex,
					blockIndex,
					blockId: block.id
				},
				definitionName: definitionType,
				errors: {}
			});
		}

		// If a validator exists for this definition, validate the block fields via it
		if(validator) {
			validator.validate(block.fields, errors => {
				if(errors) {
					// loop through errors and add them to the state
					errors.forEach(({ field, message }) => {
						fieldsWithErrors.push(field);

						commit('addFieldError', {
							blockId: `${block.id}`,
							fieldName: field,
							errors: [message]
						});
					});
				}

				// Add location to store potential errors for fields that don't
				// currently have errors, so that the state remains reactive
				fieldsWithValidation
					.forEach(fieldPath => {
						if(!fieldsWithErrors.includes(fieldPath)) {
							commit('addFieldError', {
								blockId: `${block.id}`,
								fieldName: fieldPath,
								errors: []
							});
						}
					});
			});
		}
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
					if (response.data.data.valid === 0) {
						// create the message markup

						const message = vue.$createElement(
							'div',
							{},
							[
								vue.$createElement('p', { class:'el-message__content', style:'padding-bottom:1rem' }, 'The page saved ok, but there are some validation errors.'),
								vue.$createElement('p', { class:'el-message__content', style:'padding-bottom:1rem' }, 'You won\'t be able to publish until these are fixed.'),
								vue.$createElement('a', {
									attrs: {
										href: '#'
									},
									style:'font-size:14px',
									on: {
										click(e) {
											vue.$bus.$emit('sidebar:openErrors', e);
											vue.$message.closeAll();
										}
									}
								}, 'Check the error list for details.')
							],

						);
						vue.$message({
							message: message,
							type: 'warning',
							duration: 7000,
							showClose: true
						});
					}
					// we're all good
					else if (response.data.data.valid === 1) {
						vue.$message({
							message: 'Page saved.',
							type: 'success',
							duration: 2000
						});
					}
				}
				commit('updateCurrentSavedState');
			})
			/**
			unsuccessful save, such as a network problem
			*/
			.catch((errorResponse) => {
				if (_.has(errorResponse, 'response.data.errors')) {

					// there seems to be a possibility to return multiple groups of error messages,
					// so we will cater for that
					let technicalMessages = [];

					// for each error message group...
					errorResponse.response.data.errors.forEach(error => {
						let messageLines = [];

						// add title for each group
						messageLines.push(vue.$createElement('strong', error.message));

						// add detailed error messages
						let errorsDetails = [];
						if (typeof error.details === 'string') {
							errorsDetails.push(vue.$createElement('li', error.details));
						}
						else{
							for (var key in error.details) {
								if (error.details.hasOwnProperty(key)) {
									errorsDetails.push(vue.$createElement('li', error.details[key]));
								}
							}
						}

						let errorDetailsWrapper = vue.$createElement('ul', errorsDetails);
						messageLines.push(errorDetailsWrapper);

						// create element for each group
						let technicalMessage = vue.$createElement('div', messageLines);
						technicalMessages.push(technicalMessage);
					});


					technicalMessages = vue.$createElement('div', technicalMessages);
					let message = vue.$createElement('div', [
						vue.$createElement('p', 'Some errors were found on this page. Please contact us with the following details:'),
						technicalMessages,
						vue.$createElement('p', 'ID of page being edited is: ' + id)
					]);

					vue.$notify({
						title: 'Not saved',
						message: message,
						type: 'error',
						duration: 0,
						width: '50%'
					});
				}
				else{
					// TODO: what message should we give here?
					vue.$notify({
						title: 'Not saved',
						message: 'There was a problem and this page has not been saved. Please try again later.',
						type: 'error',
						duration: 0
					});
				}
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
		return (
			state.loaded &&
			state.pageData.status ?
				state.pageData.status : ''
		);
	},

	/**
	 * Getter to retrieve the title of the current page, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page title, or null if there is no current page.
	 * @memberof state/page#
	 */
	pageTitle: (state) => {
		return (
			state.loaded &&
			state.pageData.title ?
				state.pageData.title : ''
		);
	},

	/**
	 * Getter to retrieve the slug of the current page, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page slug, or null if there is no current page.
	 * @memberof state/page#
	 */
	pageSlug: (state) => {
		return (
			state.loaded &&
			state.pageData.slug ?
				state.pageData.slug : ''
		);
	},

	/**
	 * Getter to retrieve the path of the current page, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page's path, or null if there is no current page.
	 * @memberof state/page#
	 */
	pagePath: (state) => {
		return (
			state.loaded &&
			state.pageData.path ?
				state.pageData.path : ''
		);
	},

	/**
	 * Getter to retrieve the title of the
	 * @param state
	 * @returns {string}
	 */
	siteTitle: (state) => {
		return (
			state.loaded &&
			state.pageData.site &&
			state.pageData.site.name ?
				state.pageData.site.name : ''
		);
	},

	/**
	 * Getter to retrieve the root path of the current page's site, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current page's site's path or null if there is no current page.
	 * @memberof state/page#
	 * @todo Site should be a separate object in store state.
	 */
	sitePath: (state) => {
		return (
			state.loaded &&
			state.pageData.site &&
			state.pageData.site.path ?
				state.pageData.site.path : ''
		);
	},

	/**
	 * Getter to retrieve the domain name for the current page's site, or null if no page is set.
	 * @param state
	 * @returns {string|null} The current domain name, or null if there is no current page.
	 * @memberof state/page#
	 * @todo Site should be a separate object in store state.
	 */
	siteDomain: (state) => {
		return (
			state.loaded &&
			state.pageData.site &&
			state.pageData.site.host ?
				state.pageData.site.host : ''
		);
	},

	/**
	 * Get the siteDefinitionId for the current site's definition.
	 * @param state
	 * @returns {string|null} Site definition ID in the form {name}-v{version} or null if no current site.
	 */
	siteDefinitionId: (state) => {
		return (
			state.loaded &&
			state.pageData.site &&
			state.pageData.site.site_definition_name &&
			state.pageData.site.site_definition_version ?
				(state.pageData.site.site_definition_name + '-v' + state.pageData.site.site_definition_version) :
				null
		);
	},

	/**
	 * Get the site definition for the current site.
	 * @param state
	 * @param getters
	 * @param rootState
	 * @returns {SiteDefinition|null} - The site definition for the current site, or null.
	 */
	siteDefinition: (state, getters, rootState) => {
		const id = getters.siteDefinitionId;
		return rootState.site.siteDefinitions[id] !== void 0 ? rootState.site.siteDefinitions[id] : null;
	},

	pageIsCopyable: (state, getters, rootState) => (page) => {
		if (getters.siteDefinition) {
			let pageLayout = page.layout.name + '-v' + page.layout.version;
			return getters.siteDefinition.availableLayouts.includes(pageLayout);
		}
		return false;
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
		return getDraftPreviewURL(getters.siteDomain, getters.sitePath + (getters.pagePath === '/' ? '' : getters.pagePath));
	},

	/**
	 * Get the URL at which the published version of the current page can be previewed in the editor.
	 * @param state
	 * @param getters
	 * @returns {string} Full URL
	 */
	publishedPreviewURL: (state, getters) => {
		return getPublishedPreviewURL(getters.siteDomain, getters.sitePath + (getters.pagePath === '/' ? '' : getters.pagePath));
	},

	unsavedChangesExist: (state) => () => {
		if(state.currentSavedState.length === 0) {
			// if user has not edited a page yet so we do not have any unsaved changes
			return false;
		}
		else {
			return state.currentSavedState !== JSON.stringify(state.pageData.blocks);
		}
	},

	/**
	 * Get a block referenced by its region, section and index.

	 * @param state
	 * @param {string} regionName - The name of the region containing the block.
	 * @param {number} sectionIndex - The index in the region of the section containing the block.
	 * @param {number} blockIndex - The index in the section of the block.
	 *
	 * @return {BlockData|null} - The Block data or null if it does not exist.
	 */
	getBlock: (state) => (regionName, sectionIndex, blockIndex) => {
		return (
			state.pageData && state.pageData.blocks &&
			state.pageData.blocks[regionName] &&
			sectionIndex < state.pageData.blocks[regionName].length &&
			blockIndex < state.pageData.blocks[regionName][sectionIndex].blocks.length ?
				state.pageData.blocks[regionName][sectionIndex].blocks[blockIndex] :
				null
		);
	},

	/**
	 * Get a Section by index and region name.

	 * @param state
	 * @param {string} regionName - The name of the region containing the block.
	 * @param {number} sectionIndex - The index in the region of the section containing the block.
	 *
	 * @return {Section|null} - The section or null if it does not exist.
	 */
	getSection: (state) => (regionName, sectionIndex) => {
		return (
			state.pageData &&
			state.pageData.blocks &&
			state.pageData.blocks[regionName] &&
			sectionIndex < state.pageData.blocks[regionName].length ?
				state.pageData.blocks[regionName][sectionIndex] :
				null
		);
	},

	/**
	 * Get the array of sections for a specified region.

	 * @param state
	 * @param {string} regionName - The name of the region.
	 *
	 * @return {Array|null} - An array of sections in the specified region or null if the region does not exist.
	 */
	getRegionSections: (state) => (regionName) => {
		return (
			state.pageData && state.pageData.blocks &&
			state.pageData.blocks[regionName] ?
				state.pageData.blocks[regionName] :
				null
		);
	},

	currentPageBreadcrumbs: (state, getters, rootState) => {

		let breadcrumbs = [];

		if(!rootState.page.pageData.ancestors) {
			return breadcrumbs;
		}

		let pages = _.cloneDeep(rootState.page.pageData.ancestors);
		pages.push(rootState.page.pageData);

		for (var i = 0; i < pages.length; i++) {
			let breadcrumb = {};
			breadcrumb.path = pages[i].full_path;
			if (pages[i].path === '/') {
				breadcrumb.title = pages[i].site_name;
			} else {
				breadcrumb.title = pages[i].title;
			}

			breadcrumbs.push(breadcrumb);
		}

		return breadcrumbs;
	},

    currentLayoutLabel(state) {
	    return state.currentLayoutLabel;
    }

};


const
	// helper for running stuff only in iframe
	runInIframeAfterTick = (run) => {
		if(isIframe) {
			Vue.nextTick(run);
		}
	};

export default {
	state,
	mutations,
	actions,
	getters
};
