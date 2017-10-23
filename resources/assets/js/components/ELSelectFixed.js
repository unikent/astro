/**
 * Fixes the Element UI select component change event, because it's broken currently.
 *
 * We're overriding the "value" watcher in a bit of an ugly way, but it's only
 * temporary. This is because although the "extends" keyword overrides methods
 * (like OOP), it only merges lifecylcle hooks, watchers etc. which means applying
 * a new watcher for "value" would just mean there would then be two watchers on it.
 *
 * Copied changes from this PR:
 * https://github.com/ElemeFE/element/pull/5360 (only available in beta version of Element UI 2)
 *
 * When we update to Element 2+ we can stop using this.
 */
import { Select } from 'element-ui';

const valueEquals = (a, b) => {
	if(a === b) {
		return true;
	}
	if(!(a instanceof Array && b instanceof Array) || a.length !== b.length) {
		return false;
	}
	for(let i = 0; i !== a.length; ++i) {
		if(a[i] !== b[i]) {
			return false;
		}
	}
	return true;
};

let override = {
	extends: Select,

	name: 'el-select-fixed-change-event',

	methods: {

		emitChange(val) {
			if(!valueEquals(this.value, val)) {
				this.$emit('change', val);
			}
		},

		deletePrevTag(e) {
			if(e.target.value.length <= 0 && !this.toggleLastOptionHitState()) {
				const value = this.value.slice();
				value.pop();
				this.$emit('input', value);
				this.emitChange(value);
			}
		},

		handleOptionSelect(option) {
			if(this.multiple) {
				const value = this.value.slice();
				const optionIndex = this.getValueIndex(value, option.value);
				if(optionIndex > -1) {
					value.splice(optionIndex, 1);
				}
				else if(this.multipleLimit <= 0 || value.length < this.multipleLimit) {
					value.push(option.value);
				}
				this.$emit('input', value);
				this.emitChange(value);
				if(option.created) {
					this.query = '';
					this.inputLength = 20;
				}
				if(this.filterable) {
					this.$refs.input.focus();
				}
			}
			else {
				this.$emit('input', option.value);
				this.emitChange(option.value);
				this.visible = false;
			}
			this.$nextTick(() => this.scrollToOption(option));
		},

		selectOption() {
			if(this.options[this.hoverIndex]) {
				this.handleOptionSelect(this.options[this.hoverIndex]);
			}
		},

		deleteSelected(event) {
			event.stopPropagation();
			this.$emit('input', '');
			this.emitChange('');
			this.visible = false;
			this.$emit('clear');
		},

		deleteTag(event, tag) {
			let index = this.selected.indexOf(tag);
			if(index > -1 && !this.disabled) {
				const value = this.value.slice();
				value.splice(index, 1);
				this.$emit('input', value);
				this.emitChange(value);
				this.$emit('remove-tag', tag);
			}
			event.stopPropagation();
		}

	}
};

// what I tried to explain in the top comment
override.extends.watch.value = function(val) {
	if(this.multiple) {
		this.resetInputHeight();
		if(val.length > 0 || (this.$refs.input && this.query !== '')) {
			this.currentPlaceholder = '';
		}
		else {
			this.currentPlaceholder = this.cachedPlaceHolder;
		}
	}
	this.setSelected();
	if(this.filterable && !this.multiple) {
		this.inputLength = 20;
	}
	this.dispatch('ElFormItem', 'el.form.change', val);
};

export default override;
