<?php

add_action( 'wp_enqueue_scripts', 'basel_child_enqueue_styles', 1000 );

function basel_child_enqueue_styles() {
	$version = basel_get_theme_info( 'Version' );
	
	if( basel_get_opt( 'minified_css' ) ) {
		wp_enqueue_style( 'basel-style', get_template_directory_uri() . '/style.min.css', array('bootstrap'), $version );
	} else {
		wp_enqueue_style( 'basel-style', get_template_directory_uri() . '/style.css', array('bootstrap'), $version );
	}
	
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('bootstrap'), $version );
}

/**
 * Override the default Event Cost with the ACF field 'event_cost'
 */
add_filter( 'tribe_get_cost', 'my_custom_event_cost_display', 10, 3 );

function my_custom_event_cost_display( $cost, $post_id, $with_currency_symbol ) {
    // Get the value from your ACF field
    $custom_cost = get_field( 'event_cost', $post_id );

    // If the ACF field has a value, use it. Otherwise, use the original cost.
    if ( ! empty( $custom_cost ) ) {
        return $custom_cost;
    }

    return $cost;
}



/**
 * Inject a micro-script to rewrite the back-link string directly in the DOM
 */
add_action( 'wp_footer', 'myfamilyfirst_fix_backlink_js', 999 );
function myfamilyfirst_fix_backlink_js() {
    // Only target single event pages
    if ( is_singular( 'tribe_events' ) ) {
        ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var backLink = document.querySelector('.tribe-events-back a');
                if (backLink && backLink.textContent.includes('Όλα Εκδηλώσεις')) {
                    backLink.textContent = ' « Όλες οι εκδηλώσεις';
                }
            });
        </script>
        <?php
    }
}


/**
 * DOM Fallback script to change "View RSVP" to Greek directly in the browser
 */
add_action( 'wp_footer', 'myfamilyfirst_view_rsvp_js_fallback', 999 );
function myfamilyfirst_view_rsvp_js_fallback() {
    ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Find all links, buttons, or containers on the page
            var elements = document.querySelectorAll('a, button, span, div');
            elements.forEach(function(el) {
                // If the element contains the exact English string, replace it
                if (el.childNodes.length === 1 && el.textContent.trim() === 'View RSVP') {
                    el.textContent = 'Δείτε τη συμμετοχή σας';
                }
            });
        });
    </script>
    <?php
}




/**
 * Permanent server-side fix for WPML-broken The Events Calendar URLs.
 * Strips the leaked rewrite regex "(/?:events|εκδηλώσεις)", converts Greek view
 * slugs (μήνας/σήμερα/ημέρα) to month/today/day, fixes the "&" query connector,
 * and cleans the encoded-ampersand "&#038;" artifact.
 * Uses str_replace only (cannot blank the page) with an empty-output guard.
 */

function mff_evt_fix( $str ) {
    if ( ! is_string( $str ) || '' === $str ) {
        return $str;
    }
    if ( false === strpos( $str, '(/?:events|' ) && false === strpos( $str, '(\/?:events|' ) ) {
        return $str; // nothing broken present
    }

    $map = array(
        // 1) Kill the leaked regex base — plain (href) and JSON-escaped (data block)
        '(/?:events|εκδηλώσεις)'                                   => 'events',
        '(\/?:events|\u03b5\u03ba\u03b4\u03b7\u03bb\u03ce\u03c3\u03b5\u03b9\u03c2)' => 'events',

        // 2) Convert Greek view slugs to the working English ones — plain
        '/μήνας/'  => '/month/',
        '/σήμερα/' => '/today/',
        '/ημέρα/'  => '/day/',
        // JSON-escaped view slugs
        '\/\u03bc\u03ae\u03bd\u03b1\u03c2\/'     => '\/month\/',
        '\/\u03c3\u03ae\u03bc\u03b5\u03c1\u03b1\/' => '\/today\/',
        '\/\u03b7\u03bc\u03ad\u03c1\u03b1\/'     => '\/day\/',

        // 3) Fix the "/view/&param" -> "/view/?param" connector — plain
        'events/list/&'  => 'events/list/?',
        'events/month/&' => 'events/month/?',
        'events/today/&' => 'events/today/?',
        'events/day/&'   => 'events/day/?',
        // JSON-escaped connectors
        'events\/list\/&'  => 'events\/list\/?',
        'events\/month\/&' => 'events\/month\/?',
        'events\/today\/&' => 'events\/today\/?',
        'events\/day\/&'   => 'events\/day\/?',

        // 4) Clean up the encoded-ampersand artifact (&#038;) after any view slug — plain
        'list/?#038;'  => 'list/?',
        'month/?#038;' => 'month/?',
        'today/?#038;' => 'today/?',
        'day/?#038;'   => 'day/?',
        // JSON-escaped
        'list\/?#038;'  => 'list\/?',
        'month\/?#038;' => 'month\/?',
        'today\/?#038;' => 'today\/?',
        'day\/?#038;'   => 'day\/?',
    );

    return str_replace( array_keys( $map ), array_values( $map ), $str );
}

// A) Fix the full HTML page on initial load
add_action( 'template_redirect', 'mff_evt_buffer_start', 0 );
function mff_evt_buffer_start() {
    if ( is_admin() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    ob_start( function( $html ) {
        $fixed = mff_evt_fix( $html );
        return ( is_string( $fixed ) && '' !== $fixed ) ? $fixed : $html; // never blank
    } );
}

// B) Fix AJAX/REST responses (month navigation, prev/next, search re-renders)
add_filter( 'rest_pre_echo_response', 'mff_evt_fix_rest', 10, 3 );
function mff_evt_fix_rest( $result, $server, $request ) {
    $route = method_exists( $request, 'get_route' ) ? (string) $request->get_route() : '';
    if ( false !== strpos( $route, 'tribe/views' ) && is_array( $result ) && isset( $result['html'] ) ) {
        $result['html'] = mff_evt_fix( $result['html'] );
    }
    return $result;
}

// C) Past-date search needs eventDisplay=past appended (built at submit time)
add_action( 'wp_footer', 'mff_evt_date_search_js', 999 );
function mff_evt_date_search_js() {
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? strtolower( $_SERVER['REQUEST_URI'] ) : '';
    if ( false === strpos( $uri, 'events' ) && false === strpos( $uri, 'εκδηλ' )
         && false === strpos( $uri, '%ce%b5%ce%ba%ce%b4%ce%b7%ce%bb' ) ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('submit', function(e){
        var f = e.target;
        if (!f || f.getAttribute('data-js') !== 'tribe-events-view-form') return;
        var d  = document.getElementById('tribe-events-top-bar-date');
        var kw = f.querySelector('[name="tribe-events-views[tribe-bar-search]"]');
        var date = d ? d.value : '', keyword = kw ? kw.value : '';
        if (date && date.indexOf('/') !== -1) {
            var p = date.split('/');
            if (p.length === 3) date = p[2] + '-' + p[0] + '-' + p[1];
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        var q = ['eventDisplay=past'];
        if (date)    q.push('tribe-bar-date=' + date);
        if (keyword) q.push('tribe-bar-search=' + encodeURIComponent(keyword));
        window.location.href = 'https://myfamilyfirst.gr/events/list/?' + q.join('&');
    }, true);
    </script>
    <?php
}