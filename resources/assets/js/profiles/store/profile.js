import _ from 'lodash';
import api from 'plugins/http/api';

const state = {
	loading: true,
	profileData: {
		id: null,
		username: '',
		title: '',
		first_name: '',
		last_name: '',
		roles: '',
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
		socialmedia: [],
		categories: []
	},
	definition: {
		'label': 'Edit Profile',
		'name': 'edit-profile',
		'version': 1,
		'fields': [
			{
				'type': 'text',
				'name': 'username',
				'label': 'Username',
			},
			{
				type: 'text',
				name: 'title',
				label: 'Title'
			},
			{
				type: 'text',
				name: 'first_name',
				label: 'First name'
			},
			{
				type: 'text',
				name: 'last_name',
				label: 'Last name'
			},
			{
				type: 'text',
				name: 'roles',
				label: 'Roles'
			},
			{
				type: 'text',
				name: 'email',
				label: 'Email'
			},
			{
				type: 'text',
				name: 'telephone',
				label: 'Telephone'
			},
			{
				type: 'text',
				name: 'location',
				label: 'Location'
			},
			{
				type: 'text',
				name: 'office_hours',
				label: 'Office hours'
			},
			{
				type: 'text',
				name: 'blog',
				label: 'Blog'
			},
			{
				type: 'text',
				name: 'personal_research_website',
				label: 'Personal research website'
			},
			{
				name: 'about',
				label: 'About',
				type: 'textarea'
			},
			{
				type: 'text',
				name: 'research_interest_highlights',
				label: 'Research interest highlights',
			},
			{
				name: 'research_interests',
				label: 'Research interests',
				type: 'textarea'
			},
			{
				name: 'teaching',
				label: 'Teaching',
				type: 'richtext'
			},
			{
				name: 'supervision',
				label: 'Supervision',
				type: 'textarea'
			},
			{
				name: 'professional',
				label: 'Professional',
				type: 'textarea'
			},
			{
				name: 'past_work',
				label: 'Past work',
				type: 'textarea'
			},
			{
				name: 'categories',
				label: 'Categories',
				type: 'unikent/profiles/categories'
			},
			{
				name: 'socialmedia',
				label: 'Social media',
				type: 'unikent/profiles/socialmedia'
			}
		]
	}
};

const mutations = {
	setProfileData(state, data) {
		state.profileData = data;
	},

	setLoading(state, visible) {
		state.loading = visible;
	},

	updateFieldValue(state, { name, value }) {
		let fields = state.profileData;

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
};

const actions = {
	fetchProfileData({ commit }, { siteId, profileId }) {
		api.get(`sites/${siteId}/profiles/${profileId}/draft`)
			.then(({ data: json }) => {
				let profileData = json.data;
				// flatten categories to just ids
				profileData.categories = profileData.categories.map(category => category.id);

				commit('setProfileData', profileData);
				commit('setLoading', false);
			})
			.catch((errors) => {
				console.log(errors);
				// TODO something sensible if we have no profile
			})
	}
};

const getters = {
	getFieldValue: (state) => ({ name, fallback }) => {
		return _.get(state.profileData, name, fallback);
	}
};

export default {
	namespaced: true,
	state,
	actions,
	mutations,
	getters
};
