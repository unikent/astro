<template>
<el-dialog
	:title="`${modalType}${extraTitleText} Site Profile`"
	:visible.sync="visible"
	:modal-append-to-body="false"
	:before-close="promptToSave"
>
	<el-form :model="formData">
		<el-form-item v-for="(value, key) in schema" :label="schema[key].label" :key="key">

			<el-input
				v-if="schema[key].type === 'textarea'"
				type="textarea"
				:rows="5"
				:name="key"
			>
				{{ schema[key].label }}
			</el-input>

			<div v-else-if="schema[key].type === 'richtext'" style="clear: left;">
				<rich-text v-model="formData[key]" />
			</div>

			<div v-else-if="schema[key].type === 'categories'" style="clear: left;">
				<el-checkbox-group v-model="formData[key]">

					<ul>
						<li v-for="category in availableCategories">
							<el-checkbox
								:label="category.id"
								:key="category.id"
								@change="(value) => resetChildCategories(value, category.id)"
							>
								{{ category.name }}
								<el-button
									v-if="!show[category.slug] && category.children"
									size="mini"
									@click="showSubcategories(category.slug)"
								>
									<i class="el-icon-plus el-icon-right" />
									Show subcategories
								</el-button>
							</el-checkbox>

							<ul v-if="show[category.slug] && category.children">
								<li v-for="subCategory in category.children">
									<el-checkbox
										:label="subCategory.id"
										:key="subCategory.id"
										@change="(value) => toggleParent(value, category.id)"
									>
										{{ subCategory.name }}
									</el-checkbox>
								</li>
							</ul>
						</li>
					</ul>

				</el-checkbox-group>
			</div>

			<div v-else-if="schema[key].type === 'social-media'" style="clear: left;">
				<div v-for="(data, index) in formData[key]">
					<el-select v-model="data.platform" placeholder="Social media platform">
						<el-option
							v-for="platform in availableSocialMedia"
							:label="platform.name"
							:value="platform.id"
							:key="platform.slug"
						>
						</el-option>
					</el-select>
					<el-input v-model="data.url" placeholder="URL">{{ schema[key].label }}</el-input>
					<el-button @click="removeSocialMediaPlatform(index)">Delete</el-button>
				</div>
				<el-button @click="addSocialMediaPlatform">Add social media</el-button>
			</div>

			<el-input v-else :name="key">{{ schema[key].label }}</el-input>

		</el-form-item>
	</el-form>
	<span slot="footer" class="dialog-footer">
		<el-button @click="cancel">Cancel</el-button>
		<el-button type="primary" @click="createProfile">Save profile</el-button>
	</span>
</el-dialog>
</template>

<script>
import _ from 'lodash';

import RichText from 'components/richtext';
import promptToSaveMixin from 'mixins/promptToSaveMixin';

export default {

	name: 'create-site-profile-modal',

	mixins: [
		promptToSaveMixin
	],

	components: {
		RichText
	},

	created() {
		this.blankProfile = {
			id: null,
			username: '',
			title: '',
			first_name: '',
			second_name: '',
			job_titles: '',
			email: '',
			telephone: '',
			location: '',
			office_hours: '',
			blog: '',
			personal_research_website: '',
			about: '',
			research_interest_highlights: '',
			research_interests: '',
			teaching: '',
			supervision: '',
			professional: '',
			date_published: '',
			past_work: '',
			social_media: [],
			categories: []
		};

		this.schema = {
			username: {
				label: 'Username'
			},
			title: {
				label: 'Title'
			},
			first_name: {
				label: 'First name'
			},
			second_name: {
				label: 'Second name'
			},
			job_titles: {
				label: 'Job titles'
			},
			email: {
				label: 'Email'
			},
			telephone: {
				label: 'Telephone'
			},
			location: {
				label: 'Location'
			},
			office_hours: {
				label: 'Office hours'
			},
			blog: {
				label: 'Blog'
			},
			personal_research_website: {
				label: 'Personal research website'
			},
			about: {
				label: 'About',
				type: 'textarea'
			},
			research_interest_highlights: {
				label: 'Research interest highlights',
			},
			research_interests: {
				label: 'Research interests',
				type: 'textarea'
			},
			teaching: {
				label: 'Teaching',
				type: 'richtext'
			},
			supervision: {
				label: 'Supervision',
				type: 'textarea'
			},
			professional: {
				label: 'Professional',
				type: 'textarea'
			},
			past_work: {
				label: 'Past work',
				type: 'textarea'
			},
			categories: {
				label: 'Categories',
				type: 'categories'
			},
			social_media: {
				label: 'Social media',
				type: 'social-media'
			}
		};

		this.$bus.$on('site-profile:showCreateProfileModal', ({ type, profileData }) => {
			this.type = type;
			this.visible = true;

			if(type === 'edit') {
				this.formData = { ..._.cloneDeep(this.blankProfile), ...profileData };
			}
			else {
				this.formData = _.cloneDeep(this.blankProfile);
			}

			this.initialData = JSON.stringify(this.formData);
			this.show = {};
		});
	},

	data() {
		return {
			// promptToSaveMixin defines "initialData" property
			type: 'create',
			visible: false,
			formData: {},
			show: {},
			availableSocialMedia: [
				{
					id: 1,
					name: 'Facebook',
					slug: 'facebook'
				},
				{
					id: 2,
					name: 'Twitter',
					slug: 'twitter'
				},
				{
					id: 3,
					name: 'LinkedIn',
					slug: 'linkedin'
				}
			],
			availableCategories: [
				{
					'id': 1,
					'name': 'Academic staff',
					'slug': 'academic_staff',
					'parent_id': null,
					'children': [
						{
							'id': 2,
							'name': 'Heads and directors',
							'slug': 'heads_and_directors',
							'parent_id': '1'
						},
						{
							'id': 3,
							'name': 'Research',
							'slug': 'research',
							'parent_id': '1'
						},
						{
							'id': 4,
							'name': 'Associate and assistants',
							'slug': 'associate_and_assistants',
							'parent_id': '1'
						},
						{
							'id': 5,
							'name': 'Sessional',
							'slug': 'sessional',
							'parent_id': '1'
						},
						{
							'id': 6,
							'name': 'Honorary',
							'slug': 'honorary',
							'parent_id': '1'
						},
						{
							'id': 7,
							'name': 'Emeritus',
							'slug': 'emeritus',
							'parent_id': '1'
						},
						{
							'id': 8,
							'name': 'Visiting',
							'slug': 'visiting',
							'parent_id': '1'
						},
						{
							'id': 9,
							'name': 'Supervisors',
							'slug': 'supervisors',
							'parent_id': '1'
						},
						{
							'id': 10,
							'name': 'Sports Ready Clinic',
							'slug': 'sports_ready_clinic',
							'parent_id': '1'
						},
						{
							'id': 11,
							'name': 'Director of studies',
							'slug': 'director_of_studies',
							'parent_id': '1'
						},
						{
							'id': 12,
							'name': 'Finance',
							'slug': 'finance',
							'parent_id': '1'
						},
						{
							'id': 13,
							'name': 'Management science',
							'slug': 'management_science',
							'parent_id': '1'
						},
						{
							'id': 14,
							'name': 'Marketing',
							'slug': 'marketing',
							'parent_id': '1'
						},
						{
							'id': 15,
							'name': 'People management and organisation',
							'slug': 'people_management_and_organisation',
							'parent_id': '1'
						},
						{
							'id': 16,
							'name': 'Strategy and international business',
							'slug': 'strategy_and_international_business',
							'parent_id': '1'
						}
					]
				},
				{
					'id': 17,
					'name': 'Research students',
					'slug': 'research_students',
					'parent_id': null
				},
				{
					'id': 18,
					'name': 'Technical',
					'slug': 'technical',
					'parent_id': null
				},
				{
					'id': 19,
					'name': 'Support and Administration',
					'slug': 'support_and_administration',
					'parent_id': null
				}
			],
		};
	},

	computed: {
		modalType() {
			return this.type.charAt(0).toUpperCase() + this.type.slice(1)
		},

		extraTitleText() {
			return (
				this.type === 'edit' ?
					` ${this.formData.first_name} ${this.formData.second_name}` +
					(this.formData.second_name.endsWith('s') ? '\'' : '\'s') :
					''
			);
		},

		isUnsaved() {
			return JSON.stringify(this.formData) !== this.initialData;
		}
	},

	methods: {

		toggleParent(checked, parentId) {
			if(checked && !this.formData.categories.includes(parentId)) {
				this.formData.categories.push(parentId);
			}
		},

		resetChildCategories(checked, categoryId) {
			if(!checked) {
				// get children of unchecked parent category
				const categoryChildren = this.availableCategories.find(
					category => category.id === categoryId
				).children;

				if(categoryChildren && categoryChildren.length) {
					const childIds = categoryChildren.map(
						childCategory => childCategory.id
					);

					// remove any selected subcategories that are in the
					// children of our unchecked parent category
					this.formData.categories = this.formData.categories.filter(
						catId => !childIds.includes(catId)
					);
				}
			}
		},

		createProfile() {
			// TODO: make API call to create/edit profile + error handling
			this.visible = false;
		},

		cancel() {
			this.promptToSave(success => {
				if(success) {
					this.visible = false;
				}
			});
		},

		addSocialMediaPlatform() {
			this.formData.social_media.push({
				platform: null,
				url: ''
			});
		},

		removeSocialMediaPlatform(index) {
			this.formData.social_media.splice(index, 1);
		},

		showSubcategories(categorySlug) {
			this.show = { ...this.show, [categorySlug]: true };
		}

	}
};
</script>
