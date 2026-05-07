<?php
function event_filter_shortcode() {
    error_log('EVENT FILTER SHORTCODE CALLED');
    
    // Ανίχνευση γλώσσας WPML
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'el';
    
    // Μεταφράσεις
    $translations = array(
        'el' => array(
            'title' => '🎯 Αναζήτηση Πρακτικής Άσκησης',
            'search_label' => '🔍 Αναζήτηση',
            'search_placeholder' => 'Αναζήτηση με όνομα...',
            'category_label' => '📂 Κατηγορία',
            'all_categories' => 'Όλες οι κατηγορίες',
            'start_date_label' => '📅 Από Ημερομηνία',
            'end_date_label' => '📅 Έως Ημερομηνία',
            'submit_button' => 'Αναζήτηση',
            'loading' => 'Φόρτωση αποτελεσμάτων...',
            'no_results' => '😔 Δεν βρέθηκαν προϊόντα.',
            'no_results_filter' => '😔 Δεν βρέθηκαν προϊόντα για τα επιλεγμένα φίλτρα.',
            'load_more' => '📦 Φόρτωση Περισσότερων',
            'error' => '⚠️ Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά.',
            'try_again' => 'Δοκιμάστε ξανά'
        ),
        'en' => array(
            'title' => '🎯 Search Internships',
            'search_label' => '🔍 Search',
            'search_placeholder' => 'Search by name...',
            'category_label' => '📂 Category',
            'all_categories' => 'All Categories',
            'start_date_label' => '📅 Start Date',
            'end_date_label' => '📅 End Date',
            'submit_button' => 'Search',
            'loading' => 'Loading results...',
            'no_results' => '😔 No products found.',
            'no_results_filter' => '😔 No products found for the selected filters.',
            'load_more' => '📦 Load More',
            'error' => '⚠️ Something went wrong. Please try again.',
            'try_again' => 'Try Again'
        )
    );
    
    // Επιλογή μεταφράσεων
    $t = $translations[$current_lang];
    
    ob_start(); ?>
    <!-- SHORTCODE IS RUNNING -->
    <style>
    .event-filter-wrapper {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .event-filter-form {
        background: linear-gradient(135deg, #667eea 0%, #3767a4 100%);
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
        margin-bottom: 50px;
    }
    .event-filter-form h3 {
        color: white;
        margin: 0 0 30px 0;
        font-size: 28px;
        font-weight: 600;
        text-align: center;
    }
    .filter-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    .filter-inputs-row2 {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 20px;
        align-items: end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    .filter-group label {
        color: white;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .filter-group input[type="date"] {
        padding: 14px 18px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        background: white;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .filter-group input[type="text"],
    .filter-group select {
        padding: 14px 18px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        background: white;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        width: 100%;
    }
    .filter-group input[type="date"]:focus,
    .filter-group input[type="text"]:focus,
    .filter-group select:focus {
        outline: none;
        box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .filter-submit {
        padding: 14px 35px;
        background: white;
        color: #667eea;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .filter-submit:hover {
        background: #f8f9ff;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .filter-submit:active {
        transform: translateY(-1px);
    }
    #event_filter_results {
        min-height: 200px;
    }
    #event_filter_results .products {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
        gap: 30px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #event_filter_results .product {
		padding-top:12px;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        width: 100% !important;
        margin: 0 !important;
        float: none !important;
    }
    #event_filter_results .product::before,
    #event_filter_results .product::after {
        display: none !important;
    }
    #event_filter_results .product:hover {
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    #event_filter_results .product img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.4s ease;
		border-radius:6px;
    }
	#event_filter_results .product .product__inner {
		display: flex !important;
		flex-direction: column !important;
		align-items: center !important;
		padding-bottom: 12px !important;
		justify-content: space-between;
    	height: 100%;
	}
    #event_filter_results .product:hover img {
        transform: scale(1.01);
    }
	#event_filter_results .product a.woocommerce-LoopProduct-link.woocommerce-loop-product__link {
		display: flex !important;
		flex-direction: column !important;
		align-items: center !important;
	}
    #event_filter_results .product .woocommerce-loop-product__title {
        padding: 20px 20px 10px;
		margin: 0;
		font-size: 16px;
		font-weight: 600;
		color: #333;
		line-height: 24px;
    }
    #event_filter_results .product .price {
        padding: 0 20px 20px;
        font-size: 22px;
        font-weight: 700;
        color: #667eea;
    }
    #event_filter_results .product .button {
        display: block;
        width: calc(100% - 40px);
        margin: 0 20px 20px;
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #3767a4 100%);
        color: white;
        text-align: center;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    #event_filter_results .product .button:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 40px;
    }
    .loading-spinner.active {
        display: block;
    }
    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .no-results {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9ff;
        border-radius: 16px;
        color: #666;
        font-size: 18px;
    }
    .load-more-events {
        margin: 0 auto;
        display: inline-block;
    }
    @media (max-width: 768px) {
        .filter-inputs {
            grid-template-columns: 1fr;
        }
        .filter-inputs-row2 {
            grid-template-columns: 1fr;
        }
        .filter-submit {
            width: 100%;
        }
        #event_filter_results .products {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <div class="event-filter-wrapper">
        <form id="event_filter_form" class="event-filter-form">
            <h3><?php echo esc_html($t['title']); ?></h3>
            
            <!-- Πρώτη σειρά: Search & Category -->
            <div class="filter-inputs">
                <div class="filter-group">
                    <label><?php echo esc_html($t['search_label']); ?></label>
                    <input type="text" name="search" placeholder="<?php echo esc_attr($t['search_placeholder']); ?>">
                </div>
                <div class="filter-group">
                    <label><?php echo esc_html($t['category_label']); ?></label>
                    <select name="category">
                        <option value=""><?php echo esc_html($t['all_categories']); ?></option>
                        <?php
                        // Παίρνουμε το μεταφρασμένο term ID της κατηγορίας "Μαθήματα"
                        $parent_courses_id = 149;
                        if (function_exists('icl_object_id')) {
                            $translated_id = icl_object_id(149, 'product_cat', false, $current_lang);
                            if ($translated_id) {
                                $parent_courses_id = $translated_id;
                            }
                        }
                        
                        // Παίρνουμε μόνο τις υποκατηγορίες της κατηγορίας "Μαθήματα"
                        $categories = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'hide_empty' => true,
                            'parent' => $parent_courses_id
                        ));
                        foreach($categories as $cat) {
                            echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <!-- Δεύτερη σειρά: Dates & Button -->
            <div class="filter-inputs-row2">
                <div class="filter-group">
                    <label><?php echo esc_html($t['start_date_label']); ?></label>
                    <input type="date" name="start_date">
                </div>
                <div class="filter-group">
                    <label><?php echo esc_html($t['end_date_label']); ?></label>
                    <input type="date" name="end_date">
                </div>
                <button type="submit" class="filter-submit"><?php echo esc_html($t['submit_button']); ?></button>
            </div>
        </form>
        
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p><?php echo esc_html($t['loading']); ?></p>
        </div>
        
        <div id="event_filter_results">
            <?php
            // Παίρνουμε το μεταφρασμένο term ID της κατηγορίας "Μαθήματα" για την τρέχουσα γλώσσα
            $courses_term_id = 149; // Default ID
            if (function_exists('icl_object_id')) {
                $translated_id = icl_object_id(149, 'product_cat', false, $current_lang);
                if ($translated_id) {
                    $courses_term_id = $translated_id;
                }
            }
            
            // Παίρνουμε όλες τις υποκατηγορίες της "Μαθήματα"
            $subcategories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => $courses_term_id
            ));
            
            // Δημιουργούμε array με όλα τα IDs (parent + children)
            $category_ids = array($courses_term_id);
            if (!empty($subcategories) && !is_wp_error($subcategories)) {
                foreach ($subcategories as $subcat) {
                    $category_ids[] = $subcat->term_id;
                }
            }
            
            // Εμφάνιση τελευταίων προϊόντων by default
            $default_args = array(
                'post_type' => 'product',
                'posts_per_page' => 12,
                'orderby' => 'date',
                'order' => 'DESC',
                'paged' => 1,
                'post_status' => 'publish',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $category_ids,
                        'operator' => 'IN'
                    )
                ),
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'event_start_date',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key' => 'event_end_date',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key' => 'event_start_date',
                        'value' => '',
                        'compare' => '!='
                    ),
                    array(
                        'key' => 'event_end_date',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            
            $default_query = new WP_Query($default_args);
            
            if($default_query->have_posts()){
                echo '<ul class="products columns-4">';
                
                while($default_query->have_posts()){
                    $default_query->the_post();
                    wc_get_template_part('content', 'product');
                }
                
                echo '</ul>';
                
                // Κουμπί Load More για τα default προϊόντα
                if($default_query->max_num_pages > 1) {
                    echo '<div style="text-align: center; margin-top: 40px;">';
                    echo '<button class="load-more-events filter-submit" data-page="2" data-start="" data-end="" data-search="" data-category="">';
                    echo esc_html($t['load_more']);
                    echo '</button>';
                    echo '</div>';
                }
                
                wp_reset_postdata();
            } else {
                echo '<div class="no-results">' . esc_html($t['no_results']) . '</div>';
            }
            ?>
        </div>
    </div>
    
    <script>
    jQuery(function($){
        var translations = <?php echo json_encode($t); ?>;
        
        // Load more functionality
        $(document).on('click', '.load-more-events', function(e){
            e.preventDefault();
            var button = $(this);
            var page = button.data('page');
            var start = button.data('start');
            var end = button.data('end');
            var search = button.data('search');
            var category = button.data('category');
            
            button.text(translations.try_again + '...').prop('disabled', true);
            
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'filter_events',
                    start_date: start,
                    end_date: end,
                    search: search,
                    category: category,
                    paged: page,
                    lang: '<?php echo $current_lang; ?>'
                },
                success: function(response){
                    button.remove();
                    $('#event_filter_results').append(response);
                },
                error: function(){
                    button.text(translations.try_again).prop('disabled', false);
                }
            });
        });
        
        $('#event_filter_form').on('submit', function(e){
            e.preventDefault();
            var start = $(this).find('input[name="start_date"]').val();
            var end   = $(this).find('input[name="end_date"]').val();
            var search = $(this).find('input[name="search"]').val();
            var category = $(this).find('select[name="category"]').val();
            
            $('.loading-spinner').addClass('active');
            $('#event_filter_results').html('');
            
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'filter_events',
                    start_date: start,
                    end_date: end,
                    search: search,
                    category: category,
                    paged: 1,
                    lang: '<?php echo $current_lang; ?>'
                },
                success: function(response){
                    $('.loading-spinner').removeClass('active');
                    $('#event_filter_results').html(response);
                },
                error: function(){
                    $('.loading-spinner').removeClass('active');
                    $('#event_filter_results').html('<div class="no-results">' + translations.error + '</div>');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('event_filter', 'event_filter_shortcode');

add_action('wp_ajax_filter_events', 'filter_events');
add_action('wp_ajax_nopriv_filter_events', 'filter_events');

function filter_events() {
    $start = isset($_POST['start_date']) && !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end   = isset($_POST['end_date']) && !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $search = isset($_POST['search']) && !empty($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $category = isset($_POST['category']) && !empty($_POST['category']) ? intval($_POST['category']) : 0;
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $lang = isset($_POST['lang']) ? sanitize_text_field($_POST['lang']) : 'el';
    
    // Παίρνουμε το μεταφρασμένο term ID της κατηγορίας "Μαθήματα" για την τρέχουσα γλώσσα
    $courses_term_id = 149; // Default ID
    if (function_exists('icl_object_id')) {
        $translated_id = icl_object_id(149, 'product_cat', false, $lang);
        if ($translated_id) {
            $courses_term_id = $translated_id;
        }
    }
    
    // Παίρνουμε όλες τις υποκατηγορίες της "Μαθήματα"
    $subcategories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => $courses_term_id
    ));
    
    // Δημιουργούμε array με όλα τα IDs (parent + children)
    $category_ids = array($courses_term_id);
    if (!empty($subcategories) && !is_wp_error($subcategories)) {
        foreach ($subcategories as $subcat) {
            $category_ids[] = $subcat->term_id;
        }
    }
    
    // Μεταφράσεις για AJAX responses
    $translations = array(
        'el' => array(
            'no_results' => '😔 Δεν βρέθηκαν προϊόντα για τα επιλεγμένα φίλτρα.',
            'load_more' => '📦 Φόρτωση Περισσότερων'
        ),
        'en' => array(
            'no_results' => '😔 No products found for the selected filters.',
            'load_more' => '📦 Load More'
        )
    );
    
    $t = $translations[$lang];
    
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12,
        'paged' => $paged,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish'
    );
    
    // Αν υπάρχει search term
    if($search !== '') {
        $args['s'] = $search;
    }
    
    // Αν υπάρχει συγκεκριμένη υποκατηγορία, χρησιμοποιούμε μόνο αυτήν
    // Διαφορετικά χρησιμοποιούμε όλες τις κατηγορίες (parent + children)
    if($category > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category,
                'operator' => 'IN'
            )
        );
    } else {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_ids,
                'operator' => 'IN'
            )
        );
    }
    
    // Πάντα να φέρνει μόνο προϊόντα με ημερομηνίες
    $base_meta_query = array(
        array(
            'key' => 'event_start_date',
            'compare' => 'EXISTS'
        ),
        array(
            'key' => 'event_end_date',
            'compare' => 'EXISTS'
        ),
        array(
            'key' => 'event_start_date',
            'value' => '',
            'compare' => '!='
        ),
        array(
            'key' => 'event_end_date',
            'value' => '',
            'compare' => '!='
        )
    );
    
    // Αν έχουν δοθεί ΚΑΙ ΟΙ ΔΥΟ ημερομηνίες, προσθέτουμε και φίλτρα ημερομηνιών
    if($start !== '' && $end !== '') {
        $args['meta_query'] = array(
            'relation' => 'AND',
            $base_meta_query[0],
            $base_meta_query[1],
            $base_meta_query[2],
            $base_meta_query[3],
            array(
                'key' => 'event_start_date',
                'value' => $start,
                'compare' => '>=',
                'type' => 'DATETIME'
            ),
            array(
                'key' => 'event_end_date',
                'value' => $end,
                'compare' => '<=',
                'type' => 'DATETIME'
            )
        );
    } else {
        // Χωρίς φίλτρο ημερομηνιών, απλά φέρε όσα έχουν ημερομηνίες
        $args['meta_query'] = array(
            'relation' => 'AND',
            $base_meta_query[0],
            $base_meta_query[1],
            $base_meta_query[2],
            $base_meta_query[3]
        );
    }
    
    $query = new WP_Query($args);
    
    if($query->have_posts()){
        if($paged == 1) {
            echo '<ul class="products columns-4">';
        }
        
        while($query->have_posts()){
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
        
        // Κουμπί "Φόρτωση Περισσότερων" αν υπάρχουν άλλες σελίδες
        if($query->max_num_pages > $paged) {
            if($paged == 1) {
                echo '</ul>';
            }
            echo '<div style="text-align: center; margin-top: 40px;">';
            echo '<button class="load-more-events filter-submit" ';
            echo 'data-page="' . ($paged + 1) . '" ';
            echo 'data-start="' . esc_attr($start) . '" ';
            echo 'data-end="' . esc_attr($end) . '" ';
            echo 'data-search="' . esc_attr($search) . '" ';
            echo 'data-category="' . esc_attr($category) . '">';
            echo esc_html($t['load_more']);
            echo '</button>';
            echo '</div>';
            if($paged == 1) {
                echo '<ul class="products columns-4" style="display:none;">';
            }
        }
        
        if($paged == 1 && $query->max_num_pages <= $paged) {
            echo '</ul>';
        }
        
        wp_reset_postdata();
    } else {
        if($paged == 1) {
            echo '<div class="no-results">' . esc_html($t['no_results']) . '</div>';
        }
    }
    
    wp_die();
}