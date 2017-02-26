
module.exports = {
	options: {
		stripBanners: true,
			banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
		' * <%= pkg.homepage %>\n' +
		' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
		' */\n'
	},

	main: {
		src: [
			'assets/js/src/fullcalendar/fullcalendar.js'
		],
		dest: 'assets/js/event-manager-core.js'
	},
	vendorJS: {
		src: [
			'assets/js/src/vendor/bootstrap/bootstrap.js',
			'assets/js/src/vendor/moment/moment.js',
			'assets/js/src/vendor/fullcalendar/fullcalendar.js'
		],
		dest: 'assets/js/event-manager-vendor-core.js'
	}
};
