/**
 * Gulpfile.
 *
 * Gulp with WordPress.
 *
 * Implements:
 *      1. Live reloads browser with BrowserSync.
 *      2. CSS: Sass to CSS conversion, error catching, Autoprefixing, Sourcemaps,
 *         CSS minification, and Merge Media Queries.
 *      3. JS: Concatenates & uglifies Vendor and Custom JS files.
 *      4. Images: Minifies PNG, JPEG, GIF and SVG images.
 *      5. Watches files for changes in CSS or JS.
 *      6. Watches files for changes in PHP.
 *      7. Corrects the line endings.
 *      8. InjectCSS instead of browser page reload.
 *      9. Generates .pot file for i18n and l10n.
 */

/**
 * Load WPGulp Configuration.
 */
const config = require( './gulp.config.js' );

/**
 * Load Plugins.
 *
 * Load gulp plugins and passing them semantic names.
 */
const gulp   = require( 'gulp' ); // Gulp of-course.
const fs     = require( 'fs' );

// Package config.
let pkgConfig = JSON.parse( fs.readFileSync( config.packageJSON ) );

// Utility related plugins.
const notify         = require( 'gulp-notify' ); // Sends message notification to you.
const wpPot          = require( 'gulp-wp-pot' ); // For generating the .pot file.
const sort           = require( 'gulp-sort' ); // Recommended to prevent unnecessary changes in pot-file.
const zip            = require( 'gulp-zip' );
const del            = require( 'del' );
const copy           = require( 'gulp-copy' );
const checktxtdomain = require( 'gulp-checktextdomain' ); // Checks gettext function calls for missing or incorrect text domain.

/**
 * Task: `clean`
 */
gulp.task(
    'clean',
    function( done ) {
        return del(
            [
                config.export.dest + `${pkgConfig.name}/`,
                config.export.dest + '*.zip'
            ]
        );
    }
);

/**
 * Task: `copy`
 */
gulp.task(
    'copy',
    function() {
        return gulp
            .src( config.export.src )
            .pipe( copy( config.export.dest + `${pkgConfig.name}/` ) );
    }
);

/**
 * Task: `zip`.
 *
 * Bundles theme for distribution.
 */
gulp.task('zip', () => {
    pkgConfig = JSON.parse( fs.readFileSync( config.packageJSON ) );
    return gulp
        .src( config.export.dest + `${pkgConfig.name}/**`, { base: 'build' })
        .pipe(
            zip(
                pkgConfig.name + `-${pkgConfig.version}.zip`
            )
        )
        .pipe( gulp.dest( config.export.dest ) )
        .pipe(
            notify({
                message: '\n\n✅  ===> BUNDLE — Created in build folder!\n',
                onLast: true
            })
        );
});

/**
 * Task: `bundle`
 */
gulp.task( 'bundle', gulp.series( 'copy', 'zip' ) );

/**
 * Task: `checktranslate`.
 *
 * Checks for missing translation strings.
 */
gulp.task(
    'checktranslate',
    function() {
        return gulp.src( config.watchPhp ).pipe(
            checktxtdomain(
                {
                    text_domain: config.textDomain, // Specify allowed domain(s).
                    keywords: [

                        // List keyword specifications.
                        '__:1,2d',
                        '_e:1,2d',
                        '_x:1,2c,3d',
                        'esc_html__:1,2d',
                        'esc_html_e:1,2d',
                        'esc_html_x:1,2c,3d',
                        'esc_attr__:1,2d',
                        'esc_attr_e:1,2d',
                        'esc_attr_x:1,2c,3d',
                        '_ex:1,2c,3d',
                        '_n:1,2,4d',
                        '_nx:1,2,4c,5d',
                        '_n_noop:1,2,3d',
                        '_nx_noop:1,2,3c,4d'
                    ]
                }
            )
        );
    }
);

/**
 * WP POT Translation File Generator.
 *
 * This task does the following:
 * 1. Gets the source of all the PHP files
 * 2. Sort files in stream by path or any custom sort comparator
 * 3. Applies wpPot with the variable set at the top of this file
 * 4. Generate a .pot file of i18n that can be used for l10n to build .mo file
 */
gulp.task(
    'translate',
    () => {
        return gulp
            .src( config.watchPhp )
            .pipe( sort() )
            .pipe(
                wpPot(
                    {
                        domain: config.textDomain,
                        package: config.textDomain,
                        bugReport: config.bugReport,
                        lastTranslator: config.lastTranslator,
                        team: config.team
                    }
                )
            )
            .pipe(
                gulp.dest( config.translationDestination + '/' + config.translationFile )
            )
            .pipe(
                notify( { message: '\n\n✅  ===> TRANSLATE — completed!\n', onLast: true } )
            );
    }
);


/**
 * Build task.
 */
gulp.task(
    'build',
    gulp.series(
        'clean',
        'checktranslate',
        'translate',
        'bundle',
        function( done ) {
            done();
        }
    )
);