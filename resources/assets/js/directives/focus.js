import Vue from 'vue';

Vue.directive('focus', {
	inserted(el) {
		el.focus();
	}
});

Vue.directive('el-focus', {
	inserted(el) {
		el.querySelector('.el-select__input').focus();
	}
});
