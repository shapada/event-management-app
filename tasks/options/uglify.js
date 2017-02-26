module.exports = {
	all: {
		files: {
			'assets/js/event-manager-core.min.js': ['assets/js/event-manager-core.js'],
			'assets/js/event-manager-vendor-core.min.js': ['assets/js/event-manager-vendor-core.js'],
		},
		options: {
			banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
			' * <%= pkg.homepage %>\n' +
			' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
			' * Licensed GPLv2+' +
			' */\n',
			mangle: {
				except: ['jQuery']
			}
		}
	}
};
