import axios from 'axios'

if(typeof window.Laravel !== 'undefined' && typeof window.Laravel.base !== 'undefined'){
	module.exports = axios.create({baseURL: window.Laravel.base})
}else{
	module.exports = axios;
}

