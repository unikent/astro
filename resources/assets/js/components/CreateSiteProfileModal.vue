<template>
<el-dialog
	title="Create Site Profile"
	:visible.sync="visible"
	:modal-append-to-body="false"
	class="el-dialog--large"
>
	<el-form :model="formData">
		<el-form-item v-for="(value, key) in schema" :label="schema[key].label" :key="key">

			<el-input v-if="schema[key].type === 'textarea'"  type="textarea" row="5" :name="key">{{ schema[key].label }}</el-input>
			
			<div v-else-if="schema[key].type === 'categories'">
				<br/>
				<el-checkbox-group v-model="formData[key]">
					
					<ul>
						<li v-for="category in availableCategories">
							<el-checkbox
								:label="category.id"
								:key="category.id"
								@change="(value) => resetChildCategories(value, category.id)"
							>
								{{ category.name }}
							</el-checkbox>
							<ul v-if="category.children">
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
			
			<el-input v-else :name="key">{{ schema[key].label }}</el-input>

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
				id: '',
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
					type: 'textarea'
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
			},
			availableSocialMedia: {

			},
			availableCategories: [
				{
					"id": 1,
					"name": "Academic staff",
					"slug": "academic_staff",
					"parent_id": null,
					"children": [
						{
							"id": 2,
							"name": "Heads and directors",
							"slug": "heads_and_directors",
							"parent_id": "1"
						},
						{
							"id": 3,
							"name": "Research",
							"slug": "research",
							"parent_id": "1"
						},
						{
							"id": 4,
							"name": "Associate and assistants",
							"slug": "associate_and_assistants",
							"parent_id": "1"
						},
						{
							"id": 5,
							"name": "Sessional",
							"slug": "sessional",
							"parent_id": "1"
						},
						{
							"id": 6,
							"name": "Honorary",
							"slug": "honorary",
							"parent_id": "1"
						},
						{
							"id": 7,
							"name": "Emeritus",
							"slug": "emeritus",
							"parent_id": "1"
						},
						{
							"id": 8,
							"name": "Visiting",
							"slug": "visiting",
							"parent_id": "1"
						},
						{
							"id": 9,
							"name": "Supervisors",
							"slug": "supervisors",
							"parent_id": "1"
						},
						{
							"id": 10,
							"name": "Sports Ready Clinic",
							"slug": "sports_ready_clinic",
							"parent_id": "1"
						},
						{
							"id": 11,
							"name": "Director of studies",
							"slug": "director_of_studies",
							"parent_id": "1"
						},
						{
							"id": 12,
							"name": "Finance",
							"slug": "finance",
							"parent_id": "1"
						},
						{
							"id": 13,
							"name": "Management science",
							"slug": "management_science",
							"parent_id": "1"
						},
						{
							"id": 14,
							"name": "Marketing",
							"slug": "marketing",
							"parent_id": "1"
						},
						{
							"id": 15,
							"name": "People management and organisation",
							"slug": "people_management_and_organisation",
							"parent_id": "1"
						},
						{
							"id": 16,
							"name": "Strategy and international business",
							"slug": "strategy_and_international_business",
							"parent_id": "1"
						}
					]
				},
				{
					"id": 17,
					"name": "Research students",
					"slug": "research_students",
					"parent_id": null
				},
				{
					"id": 18,
					"name": "Technical",
					"slug": "technical",
					"parent_id": null
				},
				{
					"id": 19,
					"name": "Support and Administration",
					"slug": "support_and_administration",
					"parent_id": null
				}
			],
		};
	},

	methods: {
		toggleParent(value, parentId) {
			if(value && !this.formData.categories.includes(parentId)) {
				this.formData.categories.push(parentId);
			}
		},

		resetChildCategories(value, categoryId) {
			if(!value) {
				const categoryChildren = this.availableCategories
					.find(cat => cat.id === categoryId).children;
				
				if(categoryChildren) {
					const childIds = categoryChildren.map(childCat => childCat.id);

					console.log(childIds);
			
					this.formData.categories = this.formData.categories.filter(
						catId => !childIds.includes(catId)
					);
				}
			}
		}

	}
};
</script>
