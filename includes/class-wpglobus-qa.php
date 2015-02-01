<?php

/**
 * Class WPGlobus_QA
 */
class WPGlobus_QA {

	const QA_USER_ID = 1;

	/**
	 * Handle special URLs for QA
	 * @url http://www.wpglobus.com/?wpglobus=qa
	 * @url http://www.wpglobus.com/ru/?wpglobus=qa
	 * @return string
	 */
	public static function filter__template_include() {
		return dirname( __FILE__ ) . '/template-wpglobus-qa.php';
	}

	public static function api_demo() {
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>WPGlobus QA</title>
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css"/>
			<style>
				xmp {
					margin: 0;
				}
			</style>
		</head>
		<body>
		<div class="container">
			<div class="page-header">
				<h1>
					<?php echo apply_filters( 'the_title',
						join( '', array(
							WPGlobus::tag_text( 'WPGlobus QA EN', 'en' ),
							WPGlobus::tag_text( 'WPGlobus QA RU', 'ru' ),
						) )
					); ?>
				</h1>
			</div>
			<?php
			self::_create_qa_items();

			self::_test_get_locale();
			self::_test_home_url();
			self::_test_string_parsing();
			self::_test_get_pages();

			self::_test_get_the_terms();
			self::_test_wp_get_object_terms();
			self::_test_get_terms();
			self::_test_get_term();

			self::_test_post_name();

			self::_common_for_all_languages();
			?>
		</div>
		</body>
		</html>
	<?php
	}

	/**
	 * @param string $type 'post' or 'page' or CPT
	 *
	 * @return WP_Post
	 */
	private static function _create_qa_post( $type ) {
		$post_title = join( '', array(
			WPGlobus::tag_text( "QA {$type}_title EN", 'en' ),
			WPGlobus::tag_text( "QA {$type}_title RU", 'ru' ),
		) );

		$post = get_page_by_title( $post_title, null, $type );

		if ( ! $post ) {

			$post_content = join( '', array(
				WPGlobus::tag_text( "QA {$type}_content EN", 'en' ),
				WPGlobus::tag_text( "QA {$type}_content RU", 'ru' ),
			) );

			$post_excerpt = join( '', array(
				WPGlobus::tag_text( "QA {$type}_excerpt EN", 'en' ),
				WPGlobus::tag_text( "QA {$type}_excerpt RU", 'ru' ),
			) );

			$post = get_post( wp_insert_post(
				array(
					'post_type'    => $type,
					'post_status'  => 'publish',
					'post_author'  => self::QA_USER_ID,
					'post_title'   => $post_title,
					'post_content' => $post_content,
					'post_excerpt' => $post_excerpt,
				)
			) );
		}

		return $post;

	}

	private static function _create_qa_items() {

		/**
		 * Create QA post if not exists
		 */
		$post = self::_create_qa_post( 'post' );
		?>
		<div id="<?php echo __FUNCTION__; ?>_post">
		<h2>QA Post</h2>

		<h3>Raw</h3>

		<div class="qa_post_raw well">
			<div class="qa_post_title"><?php echo $post->post_title; ?></div>

			<div class="qa_post_content"><?php echo $post->post_content; ?></div>

			<div class="qa_post_excerpt"><?php echo $post->post_excerpt; ?></div>
		</div>
		<h3>Cooked</h3>

		<div class="qa_post_cooked well">
			<div class="qa_post_title"><?php echo
				apply_filters( 'the_title', $post->post_title ); ?></div>

			<div class="qa_post_content"><?php
				echo apply_filters( 'the_title', $post->post_content ); ?></div>

			<div class="qa_post_excerpt"><?php
				echo apply_filters( 'get_the_excerpt', $post->post_excerpt ); ?></div>
		</div>

		<?php
		/**
		 * Create QA page if not exists
		 */
		$post = self::_create_qa_post( 'page' );

		?>
		<div id="<?php echo __FUNCTION__; ?>_page">
			<h2>QA Page</h2>

			<h3>Raw</h3>

			<div class="qa_post_raw well">
				<div class="qa_post_title"><?php echo $post->post_title; ?></div>

				<div class="qa_post_content"><?php echo $post->post_content; ?></div>

				<div class="qa_post_excerpt"><?php echo $post->post_excerpt; ?></div>
			</div>
			<h3>Cooked</h3>

			<div class="qa_post_cooked well">
				<div class="qa_post_title"><?php echo
					apply_filters( 'the_title', $post->post_title ); ?></div>

				<div class="qa_post_content"><?php
					echo apply_filters( 'the_title', $post->post_content ); ?></div>

				<div class="qa_post_excerpt"><?php
					echo apply_filters( 'get_the_excerpt', $post->post_excerpt ); ?></div>
			</div>

			<h2>QA Blog Description</h2>
			<?php
			$blogdescription = join( '', array(
				WPGlobus::tag_text( 'QA blogdescription EN', 'en' ),
				WPGlobus::tag_text( 'QA blogdescription RU', 'ru' ),
			) );
			update_option( 'blogdescription', $blogdescription );
			?>
			<div id="qa_blogdescription" class="well"><?php echo get_bloginfo( 'description' ); ?></div>
		</div>
	<?php
	}

	protected static function _common_for_all_languages() {
		?>
		<h2>Encode a text</h2>
		<p>Need to encode: <code>ENG, РУС</code></p>
		<p>Encoded string: <code id="tag_text"><?php
				echo ''
				     . WPGlobus::tag_text( 'ENG', 'en' )
				     . WPGlobus::tag_text( 'РУС', 'ru' );
				?></code>
		</p>
	<?php
	}

	protected static function _test_home_url() {
		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>home_url()</h2>
			<code><?php echo home_url(); ?></code>
		</div>
	<?php

	}

	protected static function _test_string_parsing() {
		?>

		<h2>Applying 'the_title' filter</h2>

		<?php

		$test_strings = array(
			'proper'                  => '{:en}ENG{:}{:ru}РУС{:}',
			'proper_swap'             => '{:ru}РУС{:}{:en}ENG{:}',
			'extra_lead'              => 'Lead {:en}ENG{:}{:ru}РУС{:}',
			'extra_trail'             => '{:en}ENG{:}{:ru}РУС{:} Trail',
			'qt_tags_proper'          => '[:en]ENG[:ru]РУС',
			'qt_tags_proper_swap'     => '[:ru]РУС[:en]ENG',
			'qt_comments_proper'      => '<!--:en-->ENG<!--:--><!--:ru-->РУС<!--:-->',
			'qt_comments_proper_swap' => '<!--:ru-->РУС<!--:--><!--:en-->ENG<!--:-->',
			'multiline'               => "{:en}ENG1\nENG2{:}{:ru}РУС1\nРУС2{:}",
			'multiline_qt_tags'       => "[:en]ENG1\nENG2[:ru]РУС1\nРУС2",
			'multiline_qt_comments'   => "<!--:en-->ENG1\nENG2<!--:--><!--:ru-->РУС1\nРУС2<!--:-->",
			'no_tags'                 => 'ENG РУС',
			'one_tag'                 => '{:en}ENG{:}',
			'one_tag_qt_tags'         => '[:en]ENG',
			'multipart'               => '{:en}ENG1{:}{:ru}РУС1{:}{:en}ENG2{:}{:ru}РУС2{:}',
		);

		?>
		<table class="table">
			<thead>
			<tr>
				<th>Input</th>
				<th>Output</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $test_strings as $test_id => $test_string ) : ?>
				<tr id="filter__the_title__<?php echo $test_id; ?>" title="filter__the_title__<?php echo $test_id; ?>">
					<td class="filter__the_title__input">
						<xmp><?php echo $test_string; ?></xmp>
					</td>
					<td class="filter__the_title__output">
						<xmp><?php echo apply_filters( 'the_title', $test_string ); ?></xmp>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php

	}

	/**
	 * To check the @see get_pages() function
	 * It is used, for example, to show a list of available pages in the "Parent Page" metabox
	 * when editing a page.
	 * Here, we display a list of first 3 pages
	 * and expect to see their titles correctly translated.
	 */
	private static function _test_get_pages() {

		/** @var WP_Post[] $all_pages */
		$all_pages = get_pages( array( 'number' => 3, 'sort_column' => 'ID' ) );

		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>get_pages()</h2>

			<div class="well">
				<?php foreach ( $all_pages as $page ) : ?>
					<div id="test__get_pages__<?php echo $page->ID; ?>">
						<?php echo $page->post_title; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php

	}

	/**
	 * @see get_the_terms();
	 */
	private static function _test_get_the_terms() {

		$terms = get_the_terms( 97, 'category' );
		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>get_the_terms()</h2>

			<p>Name and description of the category that the post ID=97 belongs to:</p>

			<p><code>get_the_terms( 97, 'category' );</code></p>
			<?php foreach ( $terms as $term ) : ?>
				<p id="test__get_the_terms__<?php echo $term->term_id; ?>">
					<code>$term->name</code> :
					<span class="test__get_the_terms__name"><?php echo $term->name; ?></span>
					<br/>
					<code>$term->description</code> :
					<span class="test__get_the_terms__description"><?php echo $term->description; ?></span>
				</p>
			<?php endforeach; ?>

			<p>Non-existing post ID:</p>

			<p>
				<code>get_the_terms( -15, 'category' )</code>
				=&gt; <span
					class="non-existing-post-id"><?php echo gettype( get_the_terms( - 15, 'category' ) ); ?></span>
			</p>

			<p>Non-existing term name:</p>

			<p>
				<code>get_the_terms( 97, 'no-such-term' )</code>
				=&gt; <span class="no-such-term"><?php echo get_class( get_the_terms( 97, 'no-such-term' ) ); ?></span>
			</p>
		</div>
	<?php

	}

	/**
	 * @see wp_get_object_terms();
	 */
	private static function _test_wp_get_object_terms() {

		$terms = wp_get_object_terms( array( 95, 97 ), 'category' );
		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>wp_get_object_terms()</h2>

			<p>Name and description of the categories that the posts ID=95 and ID=97 belong to:</p>

			<p><code>wp_get_object_terms( array( 95, 97 ), 'category' );</code></p>
			<?php foreach ( $terms as $term ) : ?>
				<p id="_test_wp_get_object_terms_<?php echo $term->term_id; ?>">
					<code>$term->name</code> :
					<span class="name"><?php echo $term->name; ?></span>
					<br/>
					<code>$term->description</code> :
					<span class="description"><?php echo $term->description; ?></span>
				</p>
			<?php endforeach; ?>

			<p>
				<code>wp_get_object_terms( array( 95, 97 ), 'category', array( 'fields' => 'names' ) );</code>
				<br>=&gt;
				<span class="fields_names"><?php
					echo esc_html( join( ', ', wp_get_object_terms( array( 95, 97 ), 'category',
						array( 'fields' => 'names' ) ) ) );
					?></span>
			</p>

			<p>
				<code>wp_get_object_terms( array( 97 ), 'no-such-term' );</code>
				<br>=&gt;
				<span class="no_such_term"><?php
					echo wp_get_object_terms( array( 97 ), 'no-such-term' )->get_error_message();
					?></span>
			</p>

		</div>
	<?php

	}

	/**
	 * @see get_terms();
	 */
	private static function _test_get_terms() {
		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>get_terms()</h2>

			<p><code>$terms = get_terms( 'category' )</code></p>
			<?php
			$terms = get_terms( 'category', array( 'name__like' => 'QA Category', 'hide_empty' => false ) );
			$term  = $terms[0];
			?>
			<p id="_test_get_terms_category">
				<code>$term->name</code> :
				<span class="name"><?php echo $term->name; ?></span>
				<br/>
				<code>$term->description</code> :
				<span class="description"><?php echo $term->description; ?></span>
			</p>

			<p><code>$terms = get_terms( 'post_tag' )</code></p>
			<?php
			$terms = get_terms( 'post_tag', array( 'name__like' => 'QA Tag', 'hide_empty' => false ) );
			$term  = $terms[0];
			?>
			<p id="_test_get_terms_post_tag">
				<code>$term->name</code> :
				<span class="name"><?php echo $term->name; ?></span>
				<br/>
				<code>$term->description</code> :
				<span class="description"><?php echo $term->description; ?></span>
			</p>

			<?php
			$terms = get_terms( 'category', array(
				'name__like' => 'QA Category',
				'fields'     => 'names',
				'hide_empty' => false
			) );
			$term  = $terms[0];
			?>
			<p>
				<code>get_terms( 'category', ['fields' => 'names'] )</code>
				<br/>
				=&gt;<span id="_test_get_terms_name_only"><?php echo $term; ?></span>
			</p>

		</div>
	<?php
	}

	/**
	 * @see get_term();
	 */
	private static function _test_get_term() {
		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>get_term()</h2>
			<?php
			/**
			 * Find term, to get its ID
			 */
			$terms = get_terms( 'category', array( 'name__like' => 'QA Category', 'hide_empty' => false ) );
			$term  = $terms[0];
			/**
			 * Get the term data by the ID we got above
			 */
			$term = get_term( $term->term_id, 'category' );
			?>
			<p><code>get_term( $term_id, 'category' )</code></p>

			<p id="_test_get_term_category">
				<code>$term->name</code> :
				<span class="name"><?php echo $term->name; ?></span>
				<br/>
				<code>$term->description</code> :
				<span class="description"><?php echo $term->description; ?></span>
			</p>
			<?php
			/**
			 * Don't filter ajax action 'inline-save-tax' from edit-tags.php page.
			 */
			$_POST['action'] = 'inline-save-tax';
			$term            = get_term( $term->term_id, 'category' );
			?>
			<p><code>$_POST['action'] = 'inline-save-tax';</code></p>

			<p id="_test_get_term_inline-save-tax">
				<code>$term->name</code> :
				<span class="name"><?php echo $term->name; ?></span>
			</p>
			<?php unset( $_POST['action'] ); ?>

		</div>
	<?php
	}

	/**
	 * @param WP_Post $post
	 */
	//	private static function _dump_post( WP_Post $post ) {
	//		var_dump( [
	//			'ID'               => $post->ID,
	//			'post_title'       => $post->post_title,
	//			'post_content'     => $post->post_content,
	//			'post_name'        => $post->post_name,
	//			'post_status'      => $post->post_status,
	//			'guid'             => $post->guid,
	//			'sample_permalink' => get_sample_permalink( $post->ID )[1],
	//		] );
	//	}

	/**
	 * @see get_sample_permalink
	 * @see wp_update_post calling...
	 * ... @see wp_insert_post
	 */
	private static function _test_post_name() {
		?>
		<div id="<?php echo __FUNCTION__; ?>">
			<h2>Test post_name (permalinks)</h2>
			<?php

			require_once ABSPATH . '/wp-admin/includes/post.php';

			$post = get_post( wp_insert_post(
				array(
					'post_author' => 1,
					'post_title'  => '{:en}Post EN{:}{:ru}Post RU{:}',
				)
			) );
			?>
			<p>post_title = <code><?php echo $post->post_title; ?></code></p>

			<p class="wpg_qa_draft">
				Draft:
				<br/>
				post_name = <code class="wpg_qa_post_name"><?php echo $post->post_name; ?></code>
				<br/>
				sample_permalink = <code class="wpg_qa_sample_permalink"><?php
					$_ = get_sample_permalink( $post->ID );
					echo $_[1];
					?></code>
			</p>
			<?php
			$post->post_status = 'publish';
			$post              = get_post( wp_update_post( $post ) );
			?>
			<p class="wpg_qa_publish">
				After publishing:
				<br/>
				post_name = <code class="wpg_qa_post_name"><?php echo $post->post_name; ?></code>
				<br/>
				sample_permalink = <code class="wpg_qa_sample_permalink"><?php
					$_ = get_sample_permalink( $post->ID );
					echo $_[1];
					?></code>
			</p>
		</div>
		<?php
		$force_delete = true;
		wp_delete_post( $post->ID, $force_delete );
	}

	private static function _test_get_locale() {
		?><h2>get_locale()</h2><?php
		?><div id="<?php echo __FUNCTION__; ?>" class="well"><?php echo get_locale(); ?></div><?php
	}

}

# --- EOF