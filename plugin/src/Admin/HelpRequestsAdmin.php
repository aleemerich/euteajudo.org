<?php
/**
 * Help requests in wp-admin.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Admin;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Agents\AgentAliases;
use EuTeAjudo\Agents\AgentWork;
use EuTeAjudo\HelpRequests\EditAudit;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\HelpRequests\Schedules;
use EuTeAjudo\HelpRequests\View;

/**
 * The native list and edit screens, adjusted: alias instead of author, triage filters, the
 * conversation with a reply form, and a status the editor cannot change by accident.
 */
final class HelpRequestsAdmin {

	public const ALIAS_USED_META = '_eta_alias_used';
	public const REPLY_ACTION    = 'eta_admin_reply';

	/**
	 * Result of the reply sent with the last save, for the redirect.
	 *
	 * @var string
	 */
	private static string $reply_result = '';

	/**
	 * Hooks the screens.
	 */
	public static function register(): void {
		add_filter( 'wp_insert_post_data', array( self::class, 'keep_status' ), 10, 2 );
		add_filter( 'manage_' . Type::POST_TYPE . '_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_' . Type::POST_TYPE . '_posts_custom_column', array( self::class, 'column_value' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( self::class, 'filters' ) );
		add_action( 'pre_get_posts', array( self::class, 'apply_filters' ) );
		add_action( 'add_meta_boxes_' . Type::POST_TYPE, array( self::class, 'boxes' ) );
		add_action( 'save_post_' . Type::POST_TYPE, array( self::class, 'reply_on_save' ), 20 );
		add_filter( 'redirect_post_location', array( self::class, 'redirect_with_result' ) );
		add_filter( 'post_updated_messages', array( self::class, 'messages' ) );
		add_action( 'admin_notices', array( self::class, 'notice' ) );
	}

	/**
	 * Saving in the editor never changes the status; only the flow does (ADR 0003).
	 *
	 * @param array<string, mixed> $data    Post data.
	 * @param array<string, mixed> $postarr Raw post array.
	 * @return array<string, mixed>
	 */
	public static function keep_status( array $data, array $postarr ): array {
		$id = (int) ( $postarr['ID'] ?? 0 );
		if ( Type::POST_TYPE !== ( $data['post_type'] ?? '' ) || $id <= 0 ) {
			return $data;
		}
		$current = get_post_status( $id );
		if ( is_string( $current ) && in_array( $current, Type::STATUSES, true ) && ! in_array( $data['post_status'] ?? '', array_merge( Type::STATUSES, array( 'trash' ) ), true ) ) {
			$data['post_status'] = $current;
		}
		return $data;
	}

	/**
	 * Replies from an administrator, with their own alias or with the request's Agent alias.
	 * The real author is always the administrator's account (user_id).
	 *
	 * @param int    $request_id Request ID.
	 * @param int    $user_id    Administrator.
	 * @param string $text       Reply text.
	 * @param bool   $as_agent   Whether to use the Agent alias of the request.
	 * @return int|\WP_Error Reply ID or error.
	 */
	public static function reply( int $request_id, int $user_id, string $text, bool $as_agent ): int|\WP_Error {
		if ( ! user_can( $user_id, 'edit_others_eta_help_requests' ) ) {
			return new \WP_Error( 'not_allowed' );
		}
		$id = Replies::add( $request_id, $user_id, $text, $as_agent ? AgentAliases::for_request( $request_id ) : null );
		if ( ! is_wp_error( $id ) ) {
			update_comment_meta( $id, self::ALIAS_USED_META, $as_agent ? 'agent' : 'own' );
		}
		return $id;
	}

	/**
	 * Query arguments for the "situation" filter.
	 *
	 * @param string $situation late or failed.
	 * @return array<string, mixed>
	 */
	public static function situation_query( string $situation ): array {
		return match ( $situation ) {
			'late'   => Schedules::late_query(),
			'failed' => array(
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Few open requests.
					array(
						'key'     => AgentWork::FAILURES_META,
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
				),
			),
			default  => array(),
		};
	}

	/**
	 * List columns.
	 *
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public static function columns( array $columns ): array {
		unset( $columns['author'], $columns['comments'], $columns['date'] );
		$columns['eta_author']   = __( 'Author', 'euteajudo' );
		$columns['eta_status']   = __( 'Status', 'euteajudo' );
		$columns['eta_activity'] = __( 'Author last wrote', 'euteajudo' );
		$columns['eta_replies']  = __( 'Replies', 'euteajudo' );
		return $columns;
	}

	/**
	 * Column values.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Request ID.
	 */
	public static function column_value( string $column, int $post_id ): void {
		$post = HelpRequests::get( $post_id );
		if ( null === $post ) {
			return;
		}
		switch ( $column ) {
			case 'eta_author':
				echo esc_html( Aliases::label( Aliases::number_of( (int) $post->post_author ) ) );
				break;
			case 'eta_status':
				$status = get_post_status_object( $post->post_status );
				echo esc_html( $status ? (string) $status->label : $post->post_status );
				$failures = AgentWork::failures( $post_id );
				if ( $failures > 0 ) {
					/* translators: %d: number of failed verifications. */
					echo '<br><strong>' . esc_html( sprintf( _n( '%d failed verification', '%d failed verifications', $failures, 'euteajudo' ), $failures ) ) . '</strong>';
				}
				break;
			case 'eta_activity':
				$active = (int) get_post_meta( $post_id, HelpRequests::AUTHOR_ACTIVE_META, true );
				echo esc_html( $active > 0 ? View::date_time( gmdate( 'Y-m-d H:i:s', $active ) ) : '' );
				break;
			case 'eta_replies':
				echo (int) get_comments_number( $post_id );
				break;
		}
	}

	/**
	 * Filter dropdowns above the list.
	 *
	 * @param string $post_type Post type of the screen.
	 */
	public static function filters( string $post_type ): void {
		if ( Type::POST_TYPE !== $post_type ) {
			return;
		}
		foreach ( array(
			Type::SEVERITY => __( 'All severities', 'euteajudo' ),
			Type::CATEGORY => __( 'All categories', 'euteajudo' ),
		) as $taxonomy => $all ) {
			wp_dropdown_categories(
				array(
					'taxonomy'        => $taxonomy,
					'name'            => $taxonomy,
					'value_field'     => 'slug',
					'show_option_all' => $all,
					'selected'        => self::param( $taxonomy ),
					'hide_empty'      => false,
					'hide_if_empty'   => false,
				)
			);
		}
		$current = self::param( 'eta_situation' );
		echo '<select name="eta_situation"><option value="">' . esc_html__( 'Any situation', 'euteajudo' ) . '</option>';
		foreach ( array(
			'late'   => __( 'No Agent reply for 24h', 'euteajudo' ),
			'failed' => __( 'Failed verification', 'euteajudo' ),
		) as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $current, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Applies the filters to the admin list query.
	 *
	 * @param \WP_Query $query Query.
	 */
	public static function apply_filters( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || Type::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}
		$tax = array();
		foreach ( array( Type::SEVERITY, Type::CATEGORY ) as $taxonomy ) {
			$slug = self::param( $taxonomy );
			if ( '' !== $slug && '0' !== $slug ) {
				$tax[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slug,
				);
			}
		}
		if ( array() !== $tax ) {
			$query->set( 'tax_query', $tax );
		}
		foreach ( self::situation_query( self::param( 'eta_situation' ) ) as $key => $value ) {
			$query->set( $key, $value );
		}
	}

	/**
	 * Edit screen boxes: status, conversation with reply form; the native boxes that would
	 * bypass the flow are removed.
	 *
	 * @param \WP_Post $post Request.
	 */
	public static function boxes( \WP_Post $post ): void {
		unset( $post );
		foreach ( array( 'commentsdiv', 'commentstatusdiv', 'authordiv', 'slugdiv', 'postexcerpt', 'submitdiv' ) as $box ) {
			remove_meta_box( $box, Type::POST_TYPE, 'normal' );
			remove_meta_box( $box, Type::POST_TYPE, 'side' );
		}
		add_meta_box( 'eta-request-status', __( 'Request', 'euteajudo' ), array( self::class, 'status_box' ), Type::POST_TYPE, 'side', 'high' );
		add_meta_box( 'eta-request-summary', __( 'Summary (not shown to the author)', 'euteajudo' ), 'post_excerpt_meta_box', Type::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'eta-request-conversation', __( 'Conversation', 'euteajudo' ), array( self::class, 'conversation_box' ), Type::POST_TYPE, 'normal', 'default' );
	}

	/**
	 * Status box with the save button.
	 *
	 * @param \WP_Post $post Request.
	 */
	public static function status_box( \WP_Post $post ): void {
		$status = get_post_status_object( $post->post_status );
		$rows   = array(
			__( 'Status', 'euteajudo' )               => $status ? (string) $status->label : $post->post_status,
			__( 'Author', 'euteajudo' )               => Aliases::label( Aliases::number_of( (int) $post->post_author ) ),
			__( 'Agent alias', 'euteajudo' )          => AgentAliases::label_for_request( $post->ID ),
			__( 'Written on', 'euteajudo' )           => View::date_time( $post->post_date_gmt ),
			__( 'Failed verifications', 'euteajudo' ) => (string) AgentWork::failures( $post->ID ),
		);
		$reason = (string) get_post_meta( $post->ID, HelpRequests::CLOSED_REASON_META, true );
		if ( '' !== $reason ) {
			$rows[ __( 'Closed by', 'euteajudo' ) ] = get_post_meta( $post->ID, HelpRequests::CLOSED_BY_META, true ) . ' (' . $reason . ')';
		}
		echo '<dl class="eta-admin-facts">';
		foreach ( $rows as $label => $value ) {
			echo '<dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
		}
		echo '</dl>';
		foreach ( EditAudit::log( $post->ID ) as $entry ) {
			$who = get_userdata( $entry['user'] );
			/* translators: 1: who edited, 2: date. */
			echo '<p class="description">' . esc_html( sprintf( __( 'Edited by %1$s on %2$s', 'euteajudo' ), $who ? $who->display_name : '?', (string) wp_date( 'Y-m-d H:i', $entry['time'] ) ) ) . '</p>';
		}
		$original = (string) get_post_meta( $post->ID, EditAudit::ORIGINAL_CONTENT_META, true );
		if ( '' !== $original ) {
			echo '<details><summary>' . esc_html__( 'Original text', 'euteajudo' ) . '</summary>' . View::paragraphs( $original ) . '</details>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
		}
		echo '<p class="description">' . esc_html__( 'The status changes with the conversation. Saving keeps the status and records the edit.', 'euteajudo' ) . '</p>';
		submit_button( __( 'Save changes', 'euteajudo' ), 'primary', 'save', false );
	}

	/**
	 * Conversation box: every reply with who really wrote it, and the reply form.
	 *
	 * @param \WP_Post $post Request.
	 */
	public static function conversation_box( \WP_Post $post ): void {
		foreach ( View::replies( $post->ID ) as $reply ) {
			$writer = get_userdata( (int) $reply->user_id );
			$real   = $writer ? $writer->display_name : '?';
			$by     = (int) $reply->user_id === (int) $post->post_author ? __( 'author', 'euteajudo' ) : ( $real === $reply->comment_author ? '' : $real );
			echo '<div class="eta-admin-reply" style="border-left:3px solid #c3c4c7;padding:4px 12px;margin:12px 0">';
			echo '<p><strong>' . esc_html( $reply->comment_author ) . '</strong>' . ( '' !== $by ? ' <span class="description">(' . esc_html( $by ) . ')</span>' : '' ) . ' · ' . esc_html( View::date_time( $reply->comment_date_gmt ) );
			if ( Replies::is_thanked( (int) $reply->comment_ID ) ) {
				echo ' · ' . esc_html__( 'marked "this helped me"', 'euteajudo' );
			}
			echo ' · <a href="' . esc_url( admin_url( 'comment.php?action=editcomment&c=' . (int) $reply->comment_ID ) ) . '">' . esc_html__( 'Edit', 'euteajudo' ) . '</a></p>';
			echo View::paragraphs( $reply->comment_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
			$original = (string) get_comment_meta( (int) $reply->comment_ID, EditAudit::ORIGINAL_CONTENT_META, true );
			if ( '' !== $original ) {
				echo '<details><summary>' . esc_html__( 'Original text', 'euteajudo' ) . '</summary>' . View::paragraphs( $original ) . '</details>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
			}
			echo '</div>';
		}
		if ( Type::CLOSED === $post->post_status ) {
			echo '<p>' . esc_html__( 'This request is closed. Only the author can reopen it.', 'euteajudo' ) . '</p>';
			return;
		}
		$own = Aliases::label( Aliases::number_of( get_current_user_id() ) );
		wp_nonce_field( self::REPLY_ACTION, '_eta_admin_reply_nonce' );
		echo '<p><label for="eta-admin-reply-text"><strong>' . esc_html__( 'Reply', 'euteajudo' ) . '</strong></label></p>';
		echo '<textarea id="eta-admin-reply-text" name="eta_reply_text" rows="5" class="large-text"></textarea>';
		echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Reply as', 'euteajudo' ) . '</legend>';
		/* translators: %s: administrator's alias. */
		echo '<label><input type="radio" name="eta_reply_as" value="own" checked> ' . esc_html( sprintf( __( 'Reply as %s (you)', 'euteajudo' ), $own ) ) . '</label><br>';
		/* translators: %s: Agent alias of this request. */
		echo '<label><input type="radio" name="eta_reply_as" value="agent"> ' . esc_html( sprintf( __( 'Reply as %s (Agent alias in this request)', 'euteajudo' ), AgentAliases::label_for_request( $post->ID ) ) ) . '</label>';
		echo '</fieldset>';
		echo '<p><button type="submit" name="eta_send_reply" value="1" class="button button-primary">' . esc_html__( 'Send reply', 'euteajudo' ) . '</button></p>';
	}

	/**
	 * Sends the reply typed in the conversation box when the editor is saved with the
	 * "Send reply" button. The post form is the only form on the screen, so the reply travels
	 * with it to post.php.
	 *
	 * @param int $post_id Request ID.
	 */
	public static function reply_on_save( int $post_id ): void {
		if ( ! isset( $_POST['eta_send_reply'], $_POST['_eta_admin_reply_nonce'] ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		unset( $_POST['eta_send_reply'] ); // The reply changes the status, which saves the post again.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_eta_admin_reply_nonce'] ) ), self::REPLY_ACTION ) || ! current_user_can( 'edit_post', $post_id ) ) {
			self::$reply_result = 'not_allowed';
			return;
		}
		$text               = isset( $_POST['eta_reply_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['eta_reply_text'] ) ) : '';
		$as_agent           = isset( $_POST['eta_reply_as'] ) && 'agent' === $_POST['eta_reply_as'];
		$result             = self::reply( $post_id, get_current_user_id(), $text, $as_agent );
		self::$reply_result = is_wp_error( $result ) ? (string) $result->get_error_code() : 'ok';
	}

	/**
	 * Carries the reply result to the edit screen after saving.
	 *
	 * @param string $location Redirect URL.
	 */
	public static function redirect_with_result( string $location ): string {
		return '' === self::$reply_result ? $location : add_query_arg( 'eta_replied', self::$reply_result, $location );
	}

	/**
	 * Save messages for requests.
	 *
	 * @param array<string, array<int, string|false>> $messages Messages per post type.
	 * @return array<string, array<int, string|false>>
	 */
	public static function messages( array $messages ): array {
		$saved                          = __( 'Help request saved.', 'euteajudo' );
		$messages[ Type::POST_TYPE ]    = array_fill( 0, 11, $saved );
		$messages[ Type::POST_TYPE ][0] = '';
		return $messages;
	}

	/**
	 * Result notice after a reply.
	 */
	public static function notice(): void {
		$result = self::param( 'eta_replied' );
		if ( '' === $result ) {
			return;
		}
		$ok = 'ok' === $result;
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $ok ? 'success' : 'error' ),
			esc_html( $ok ? __( 'Reply sent.', 'euteajudo' ) : __( 'The reply was not sent. Check that it is not empty and that the request is open.', 'euteajudo' ) )
		);
	}

	/**
	 * Query string parameter.
	 *
	 * @param string $key Key.
	 */
	private static function param( string $key ): string {
		return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filters.
	}
}
