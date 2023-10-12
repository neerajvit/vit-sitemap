<?php
/**
 *  HTML Sitemap Page
**/
    $exclude_ids = array();
    if(!empty(get_option( 'exclude_ids' ))){
        $exclude_ids = explode(',', get_option( 'exclude_ids' ));
    }
    if(!empty(get_option( 'new_tab_opening' ))){
        $new_tab_opening = '_blank';
    }else{
        $new_tab_opening = '_self';
    }


    $args = array(
        'title_li' => '', 
        'echo' => 0, 
        'exclude' => implode(',', $exclude_ids),
        'walker'   => new Custom_Walker_Page(),

    );
    $pages_list = wp_list_pages($args);

    $args = array(
      'post_type' => 'post',
      'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    $posts_list = '';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $category = get_the_category();
            $posts_list .= '<li><a target="' . $new_tab_opening  . '" href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
        }
    }
    wp_reset_postdata();

    $sitemap_html = '<div class="sitemap_col_2" id="sitemap_col_2"><div class="sitemap_col"><h2>Pages</h2><ul class="pages">' . $pages_list . '</ul></div>';

    $sitemap_html .= '<div class="sitemap_col"><h2>Posts</h2><ul class="posts">' . $posts_list . '</ul></div></div>';

    echo $sitemap_html;


?>
