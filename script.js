/**
 * Permanent server-side fix for WPML-broken The Events Calendar URLs.
 * Strips the leaked rewrite regex "(/?:events|εκδηλώσεις)", converts Greek view
 * slugs (μήνας/σήμερα/ημέρα) to month/today/day, fixes the "&" query connector,
 * and cleans the encoded-ampersand "&#038;" artifact.
 * Uses str_replace only (cannot blank the page) with an empty-output guard.
 */

/**
 * Fix for WPML + The Events Calendar URL rewrites and Past Event 404 searches.
 */

// 1) Fix WPML view rewrite slug leaks in buffer/REST output
function mff_evt_fix( $str ) {
    if ( ! is_string( $str ) || '' === $str ) {
        return $str;
    }
    if ( false === strpos( $str, '(/?:events|' ) && false === strpos( $str, '(\/?:events|' ) ) {
        return $str;
    }

    $map = array(
        '(/?:events|εκδηλώσεις)'                                   => 'εκδηλώσεις',
        '(\/?:events|\u03b5\u03ba\u03b4\u03b7\u03bb\u03ce\u03c3\u03b5\u03b9\u03c2)' => 'εκδηλώσεις',

        '/μήνας/'  => '/month/',
        '/σήμερα/' => '/today/',
        '/ημέρα/'  => '/day/',
        '\/\u03bc\u03ae\u03bd\u03b1\u03c2\/'     => '\/month\/',
        '\/\u03c3\u03ae\u03bc\u03b5\u03c1\u03b1\/' => '\/today\/',
        '\/\u03b7\u03bc\u03ad\u03c1\u03b1\/'     => '\/day\/',

        'list/?#038;'  => 'list/?',
        'month/?#038;' => 'month/?',
        'list\/?#038;'  => 'list\/?',
        'month\/?#038;' => 'month\/?',
    );

    return str_replace( array_keys( $map ), array_values( $map ), $str );
}

add_action( 'template_redirect', 'mff_evt_buffer_start', 0 );
function mff_evt_buffer_start() {
    if ( is_admin() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    ob_start( function( $html ) {
        $fixed = mff_evt_fix( $html );
        return ( is_string( $fixed ) && '' !== $fixed ) ? $fixed : $html;
    } );
}

add_filter( 'rest_pre_echo_response', 'mff_evt_fix_rest', 10, 3 );
function mff_evt_fix_rest( $result, $server, $request ) {
    $route = method_exists( $request, 'get_route' ) ? (string) $request->get_route() : '';
    if ( false !== strpos( $route, 'tribe/views' ) && is_array( $result ) && isset( $result['html'] ) ) {
        $result['html'] = mff_evt_fix( $result['html'] );
    }
    return $result;
}

// 2) Prevent 404 on past-date search queries
add_action( 'pre_get_posts', 'mff_fix_past_events_query', 5 );
function mff_fix_past_events_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // Check if we are requesting an events view with past/date queries
    $event_display = isset( $_GET['eventDisplay'] ) ? sanitize_text_field( $_GET['eventDisplay'] ) : '';
    $bar_date      = isset( $_GET['tribe-bar-date'] ) ? sanitize_text_field( $_GET['tribe-bar-date'] ) : '';

    if ( 'past' === $event_display || ! empty( $bar_date ) ) {
        if ( isset( $query->query_vars['post_type'] ) && 'tribe_events' === $query->query_vars['post_type'] ) {
            // Prevent 404 status override by forcing valid archive status
            $query->is_404      = false;
            $query->is_archive  = true;
            $query->is_singular = false;
            
            // Set order to descending so past events are displayed properly
            if ( 'past' === $event_display ) {
                $query->set( 'order', 'DESC' );
            }
        }
    }
}