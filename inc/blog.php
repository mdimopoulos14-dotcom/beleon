<?php
/**
 * Blog (journal): post cards, reading time and article helpers.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Estimated reading time in minutes.
 *
 * @param int $id Post ID.
 * @return int
 */
function beleon_reading_time( $id = 0 ) {
	$text  = wp_strip_all_tags( get_post_field( 'post_content', $id ? $id : get_the_ID() ) );
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) round( $words / 200 ) );
}

/**
 * Meta line: category · date · reading time.
 *
 * @param int $id Post ID.
 * @return string
 */
function beleon_post_meta( $id = 0 ) {
	$id    = $id ? $id : get_the_ID();
	$parts = array();
	$cats  = get_the_category( $id );
	if ( $cats && 'uncategorized' !== $cats[0]->slug ) {
		$parts[] = '<span class="bl-post__cat">' . esc_html( $cats[0]->name ) . '</span>';
	}
	$parts[] = '<time datetime="' . esc_attr( get_the_date( 'c', $id ) ) . '">' . esc_html( get_the_date( '', $id ) ) . '</time>';
	/* translators: %d: minutes */
	$parts[] = '<span>' . esc_html( sprintf( _n( '%d min read', '%d min read', beleon_reading_time( $id ), 'beleon-tours' ), beleon_reading_time( $id ) ) ) . '</span>';
	return '<p class="bl-post__meta">' . implode( '<span class="bl-post__dot" aria-hidden="true"></span>', $parts ) . '</p>';
}

/**
 * Post card.
 *
 * @param int  $id      Post ID.
 * @param bool $feature Large, side-by-side card.
 * @return string
 */
function beleon_render_post_card( $id, $feature = false ) {
	$url     = get_permalink( $id );
	$excerpt = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), $feature ? 42 : 24 );
	$h       = '<article class="bl-post' . ( $feature ? ' bl-post--feature' : '' ) . ' bl-m-fade-up">';
	$h      .= '<a class="bl-post__media" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">';
	$h      .= has_post_thumbnail( $id ) ? beleon_img( get_post_thumbnail_id( $id ), $feature ? 'beleon-wide' : 'beleon-card', array( 'alt' => '', 'sizes' => $feature ? '(min-width: 1024px) 60vw, 100vw' : '(min-width: 1024px) 33vw, 100vw' ) ) : '<span class="bl-ph"></span>';
	$h      .= '</a><div class="bl-post__body">' . beleon_post_meta( $id );
	$h      .= '<h2 class="bl-post__title"><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h2>';
	$h      .= '<p class="bl-post__excerpt">' . esc_html( $excerpt ) . '</p>';
	$h      .= '<span class="bl-post__more" aria-hidden="true">' . esc_html__( 'Read the story', 'beleon-tours' ) . beleon_icon( 'arrow' ) . '</span>';
	return $h . '</div></article>';
}
