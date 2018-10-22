import _ from 'lodash';
import inlineFieldMixin from 'mixins/inlineFieldMixin';
import imagesLoaded from 'imagesloaded';
import { eventBus } from 'plugins/eventbus';
import { imageUrl } from 'classes/helpers';

export default {

	props: [
		'type',
		'index',
		'fields',
		'other'
	],

	mixins: [inlineFieldMixin],

	data() {
		return { ...this.fields };
	},

	created() {
		this.fieldElements = {};
		this.watching = {};
		this.watchFields(this.fields);

		// should only be triggered when all fields are overwitten
		this.$watch('fields', () => {
			this.watchFields(this.fields);
		});
	},

	methods: {
		imageUrl(url, defaultUrl) {
			return imageUrl(url, defaultUrl);
		},

		watchFields(fields) {
			Object.keys(fields).map((name) => {
				if(!this.watching[name]) {
					this.watching[name] = true;

					this.$watch(`fields.${name}`, (newVal) => {
						if(this.internalChange) {
							this.internalChange = false;
							return;
						}

						this[name] = newVal;

						this.updateOverlays();
					}, {
						deep: true
					});
				}
			});
		},

		updateOverlays: _.throttle(
			function() {
				eventBus.$emit('block:hideHoverOverlay');
				eventBus.$emit('block:updateBlockOverlays');
				const imgs = imagesLoaded(this.$el);
				imgs.on('always', () => {
					eventBus.$emit('block:updateBlockOverlays');
				});
			},
			50,
			{ trailing: true }
		)
	}

};
