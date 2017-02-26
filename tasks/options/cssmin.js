module.exports = {
	options: {
		shorthandCompacting: false,
    	roundingPrecision: -1,
		banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
		' * <%=pkg.homepage %>\n' +
		' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
		' * Licensed GPLv2+' +
		' */\n'
	},
	// minify: {
	// 	expand: true,

	// 	cwd: 'assets/css/src',
	// 	src: ['vendor/*/*.css', '!vendor/*/*.min.css'],

	// 	dest: 'assets/css',
	// 	ext: '.min.css'
	// },
	combine : {
        files: {
            'assets/css/event-manager-core.min.css': ['assets/css/src/*.css', '!assets/css/src/*.min.css' ],
            'assets/css/event-manager-vendor-core.min.css': ['assets/css/src/vendor/*/*.css', '!assets/css/src/vendor/*/*.min.css' ],
        }
      }
};
