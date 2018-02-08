import _ from 'lodash';
import locale from 'element-ui/lib/locale/lang/en';

export default _.merge(locale, {
	el: {
		pagination: {
			pagesize: '',
			total: '{total} Results found',
		},
		upload: {
			continue: 'Continue'
		}
	}
});
