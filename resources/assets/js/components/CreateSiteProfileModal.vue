<template>
<el-dialog
	title="Create Site Profile"
	:visible.sync="visible"
	:modal-append-to-body="false"
	class="el-dialog--large"
>
	<el-form :model="formData">
		<el-form-item v-for="(value, key) in formData" :label="schema[key].label" :key="key">
			<el-input :name="key">{{ schema[key].label }}</el-input>
		</el-form-item>
	</el-form>
	<span slot="footer" class="dialog-footer">
		<el-button>Cancel</el-button>
		<el-button type="primary">Add profile</el-button>
	</span>
</el-dialog>
</template>

<script>
export default {

	name: 'create-site-profile-modal',

	created() {
		this.$bus.$on('site-profile:showCreateProfileModal', () => {
			this.visible = true;
		}) 
	},

	data() {
		return {
			formData: {
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
				past_work: '',

				categories: [],
				solical_media: []
			},
			visible: false,
			schema: {
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
					label: 'About'
				},
				research_interest_highlights: {
					label: 'Research interest highlights'
				},
				research_interests: {
					label: 'Research interests'
				},
				teaching: {
					label: 'Teaching'
				},
				supervision: {
					label: 'Supervision'
				},
				professional: {
					label: 'Professional'
				},
				past_work: {
					label: 'Past work'
				},
				categories: {
					label: 'Categories'
				},
				solical_media: {
					label: 'Social media'
				}
			},
			available_categories: [

			],
			available_solical_media_platforms: {

			}
		};
	}
};
</script>
