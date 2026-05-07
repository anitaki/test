<?php
// Shortcode για τα 9 τελευταία events
function recent_events_shortcode($atts) {
    // Attributes με default values
    $atts = shortcode_atts(array(
        'count' => 9,  // Πόσα προϊόντα να δείξει (default 9)
    ), $atts);
    
    ob_start();
    ?>
    <style>
    .recent-events-wrapper {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .recent-events-wrapper .products {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
        gap: 30px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
	span.woocommerce-Price-amount.amount{
		font-size: 15px;
    	color: black;
	}
    .recent-events-wrapper .product {
        background: white;
		padding: 0px 0px 12px !important;
		border-radius: 0px;
		overflow: hidden;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
		transition: all 0.3s ease;
		position: relative;
		width: 100% !important;
		margin: 0 !important;
		float: none !important;
		border: 1px solid #dfdddd;
    }
    .recent-events-wrapper .product::before,
    .recent-events-wrapper .product::after {
        display: none !important;
    }
    .recent-events-wrapper .product:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    .recent-events-wrapper .product img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .recent-events-wrapper .product:hover img {
        transform: scale(1.02);
    }
    .recent-events-wrapper .product .woocommerce-loop-product__title {
        padding: 0px 20px 10px;
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #333;
	    border-bottom: 1px solid #46464621;
		margin-bottom: 8px;
    }
    .recent-events-wrapper .product .price {
        padding: 0 20px 20px;
        font-size: 22px;
        font-weight: 700;
        color: #667eea;
    }
	.recent-events-wrapper .product-category {
		padding: 12px 20px 0px 20px;
	}    
	.recent-events-wrapper .product .button {
      
		display:none !important;
    }
    .recent-events-wrapper .product .button:hover {
        transform: scale(1.01);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    @media (max-width: 768px) {
        .recent-events-wrapper .products {
            grid-template-columns: 1fr !important;
        }
    }
    </style>
    
   <div class="recent-events-wrapper">
        <?php
        // Παίρνουμε το μεταφρασμένο term ID για το WPML
        $term_id = 149;
        if (function_exists('wpml_object_id_filter')) {
            $term_id = wpml_object_id_filter($term_id, 'product_cat', true);
        }
        
        // Παίρνουμε όλα τα child terms (subcategories)
        $child_terms = get_term_children($term_id, 'product_cat');
        
        // Δημιουργούμε array με το parent term και όλα τα children
        $all_terms = array_merge(array($term_id), $child_terms);
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => intval($atts['count']),
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $all_terms,
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
        
        $query = new WP_Query($args);
        
        if($query->have_posts()){
            echo '<ul class="products columns-3">';
            
            while($query->have_posts()){
                $query->the_post();
                
                // Παίρνουμε την κατηγορία
                $product_id = get_the_ID();
                $terms = get_the_terms($product_id, 'product_cat');
                $category_html = '';
                
                if($terms && !is_wp_error($terms)) {
                    $category_names = array();
                    foreach($terms as $term) {
                        $category_names[] = $term->name;
                    }
                    $category_html = '<div class="product-category">' . implode(', ', $category_names) . '</div>';
                }
                
                // Ξεκινάμε output buffering για να πιάσουμε το template
                ob_start();
                wc_get_template_part('content', 'product');
                $product_html = ob_get_clean();
                
                // Προσθέτουμε την κατηγορία πριν τον τίτλο
                $product_html = preg_replace(
                    '/(<h2 class="woocommerce-loop-product__title">)/',
                    $category_html . '$1',
                    $product_html
                );
                
                echo $product_html;
            }
            
            echo '</ul>';
            wp_reset_postdata();
        } else {
            echo '<p style="text-align: center; color: #666;">Δεν βρέθηκαν πρόσφατες εκδηλώσεις.</p>';
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('recent_events', 'recent_events_shortcode');