<?php
/*
  Plugin Name: Moxie Movies
 * Description: Simple API for Moxie's development test.
 * Author: Lakhai.
 * Version: 1
 * Author URI: http://lakhai.in/
 */
global $instance;
$instance = new MoxieMovie();

class MoxieMovie {
	public function __construct() {
		add_action( 'init', array( $this, 'add_post_type' ) );
		add_action( 'add_meta_boxes', function() {
			add_meta_box( 'moxie_meta_box', 'Movie Information', array( $this, 'meta_box' ), 'movie', 'normal', 'high' );
		});
		add_action( 'save_post', array( $this, 'save_movie_info' ) );
	}

	public function add_post_type() {
		$labels = array(
			'name'               => __( 'Movies', 'moxie' ),
			'singular_name'      => __( 'Movie', 'moxie' ),
			'menu_name'          => __( 'Movies', 'moxie' ),
			'name_admin_bar'     => __( 'Movie', 'moxie' ),
			'add_new'            => __( 'Add New', 'Movie', 'moxie' ),
			'add_new_item'       => __( 'Add New Movie', 'moxie' ),
			'new_item'           => __( 'New Movie', 'moxie' ),
			'edit_item'          => __( 'Edit Movie', 'moxie' ),
			'view_item'          => __( 'View Movie', 'moxie' ),
			'all_items'          => __( 'All Movies', 'moxie' ),
			'search_items'       => __( 'Search Movies', 'moxie' ),
			'parent_item_colon'  => __( 'Parent Movies:', 'moxie' ),
			'not_found'          => __( 'No movies found.', 'moxie' ),
			'not_found_in_trash' => __( 'No movies found in Trash.', 'moxie' )
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Save movies with a little info about them.', 'moxie' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'movie' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author' ),
			'menu_icon' => 'dashicons-video-alt',
		);
		register_post_type( __( 'movie', 'moxie' ), $args );
	}

	public function meta_box() {
		global $post;

		$movie_info = $this->get_movie_info( $post->ID );
	?>
		<input type="hidden" name="movie_nonce" id="movie_nonce" value="<?php echo wp_create_nonce( 'movie_nonce' ); ?>">
		<label for="poster_url"><?php echo __( 'Poster URL', 'moxie' ); ?></label>
		<input id="poster_url" type="text" value="<?php echo $movie_info['poster_url']; ?>" name="movie[poster_url]"><br>
		<label for="year"><?php echo __( 'Year', 'moxie' ); ?></label>
		<input id="year" type="date" value="<?php echo $movie_info['year']; ?>" name="movie[year]"><br>
		<label for="rating"><?php echo __( 'Rating', 'moxie' ); ?></label>
		<input id="rating" name="movie[rating]" type="number" max="5" min="1" value="<?php echo $movie_info['rating']; ?>"><br>
		<label for="description"><?php echo __( 'Description', 'moxie' ); ?></label><br>
		<textarea name="movie[description]" id="description"><?php echo $movie_info['description']; ?></textarea>
	<?php
	}

	private function get_movie_info($id) {
		if( ! $id ) {
			return false;
		}

		$movie['poster_url']  = get_post_meta( $id, 'poster_url', true );
		$movie['rating'] 	  = get_post_meta( $id, 'rating', true );
		$movie['year'] 		  = get_post_meta( $id, 'year', true );
		$movie['description'] = get_post_meta( $id, 'description', true );

		return $movie;
	}

	public function save_movie_info($id) {
		if ( ! wp_verify_nonce( $_POST['movie_nonce'], 'movie_nonce' ) ) {
			return false;
		}
			
		if ( is_array( $_POST['movie'] ) && ! empty( $_POST['movie'] ) ) {
			foreach ( $_POST['movie'] as $key => $value ) {
				update_post_meta( $id, $key, $value );
			}
		}
	}

	static function get_movies() {
		$movies = array();
		$posts  = get_posts( array( 'post_type' => 'movie', 'posts_per_page' => 20 ) );

		if ( is_array( $posts ) && ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				setup_postdata($post);

				$current_movie			= $this->get_movie_info( $post->ID );
				$current_movie['id'] 	= $post->ID;
				$current_movie['title'] = $post->post_title;

				$movies[] = $current_movie;
			}
		}

		return $movies;
	}


}
