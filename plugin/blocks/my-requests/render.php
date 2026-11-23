<?php
/**
 * My requests: the list, or one conversation when the address has a code.
 *
 * @package EuTeAjudo
 */

use EuTeAjudo\Accounts\Forms\Messages;
use EuTeAjudo\Accounts\Forms\View as FormView;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\HelpRequests\Conversations;
use EuTeAjudo\HelpRequests\HelpRequestType;
use EuTeAjudo\HelpRequests\Replies;
use EuTeAjudo\HelpRequests\View;

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	return;
}
$euteajudo_code    = Conversations::current_code();
$euteajudo_request = '' === $euteajudo_code ? null : Conversations::visible_request( $euteajudo_code );
$euteajudo_viewer  = get_current_user_id();
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'eta-account eta-app' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core escapes. ?>>
<?php if ( null === $euteajudo_request ) : ?>
	<?php
	$euteajudo_list = get_posts(
		array(
			'post_type'   => HelpRequestType::POST_TYPE,
			'post_status' => HelpRequestType::STATUSES,
			'author'      => $euteajudo_viewer,
			'numberposts' => 50,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);
	echo FormView::error_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View.
	?>
	<p><a class="eta-button wp-element-button eta-button-inline" href="<?php echo esc_url( Pages::url( Pages::WRITE ) ); ?>"><?php esc_html_e( 'New request', 'euteajudo' ); ?></a></p>
	<?php if ( array() === $euteajudo_list ) : ?>
		<p class="eta-note"><?php esc_html_e( 'Nothing here yet. When you want to write, just start.', 'euteajudo' ); ?></p>
	<?php else : ?>
		<ul class="eta-requests">
			<?php foreach ( $euteajudo_list as $euteajudo_item ) : ?>
				<?php $euteajudo_label = View::status_label( $euteajudo_item ); ?>
				<li class="<?php echo esc_attr( HelpRequestType::CLOSED === $euteajudo_item->post_status ? 'is-closed' : '' ); ?>">
					<a href="<?php echo esc_url( Conversations::url( $euteajudo_item ) ); ?>">
						<span class="eta-r-title"><?php echo esc_html( get_the_title( $euteajudo_item ) ); ?></span>
						<span class="eta-r-meta">
							<span class="eta-status<?php echo __( 'New reply', 'euteajudo' ) === $euteajudo_label ? ' eta-status-new' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static class names. ?>"><?php echo esc_html( $euteajudo_label ); ?></span>
							<time datetime="<?php echo esc_attr( $euteajudo_item->post_date_gmt ); ?>"><?php echo esc_html( View::date( $euteajudo_item->post_date_gmt ) ); ?></time>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
<?php else : ?>
	<?php
	$euteajudo_is_author = (int) $euteajudo_request->post_author === $euteajudo_viewer;
	$euteajudo_closed    = HelpRequestType::CLOSED === $euteajudo_request->post_status;
	$euteajudo_replies   = View::replies( $euteajudo_request->ID );
	$euteajudo_step      = Messages::current_step();
	?>
	<a class="eta-back" href="<?php echo esc_url( Pages::url( Pages::MY_REQUESTS ) ); ?>"><?php esc_html_e( 'My requests', 'euteajudo' ); ?></a>
	<h2 class="eta-request-title"><?php echo esc_html( get_the_title( $euteajudo_request ) ); ?></h2>
	<p class="eta-note">
		<?php
		/* translators: %s: date the request was written. */
		echo esc_html( sprintf( __( 'Written on %s', 'euteajudo' ), View::date( $euteajudo_request->post_date_gmt ) ) );
		?>
	</p>
	<?php
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the view helpers.
	echo FormView::error_notice();
	if ( 'sent' === $euteajudo_step ) {
		echo FormView::notice( __( 'Request sent. The reply may take a few hours; we let you know by email.', 'euteajudo' ) );
	}
	// phpcs:enable
	?>
	<article class="eta-msg eta-msg-mine eta-msg-request"><?php echo View::paragraphs( $euteajudo_request->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?></article>
	<?php foreach ( $euteajudo_replies as $euteajudo_reply ) : ?>
		<?php $euteajudo_mine = (int) $euteajudo_reply->user_id === (int) $euteajudo_request->post_author; ?>
		<article id="reply-<?php echo (int) $euteajudo_reply->comment_ID; ?>" class="eta-msg <?php echo $euteajudo_mine ? 'eta-msg-mine' : 'eta-msg-other'; ?>">
			<header class="eta-msg-head">
				<span class="eta-who"><?php echo esc_html( $euteajudo_mine && $euteajudo_is_author ? __( 'You', 'euteajudo' ) : $euteajudo_reply->comment_author ); ?></span>
				<time datetime="<?php echo esc_attr( $euteajudo_reply->comment_date_gmt ); ?>"><?php echo esc_html( View::date_time( $euteajudo_reply->comment_date_gmt ) ); ?></time>
			</header>
			<?php echo View::paragraphs( $euteajudo_reply->comment_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
			<?php if ( ! $euteajudo_mine && $euteajudo_is_author ) : ?>
				<?php $euteajudo_thanked = Replies::is_thanked( (int) $euteajudo_reply->comment_ID ); ?>
				<?php echo FormView::open( 'thanks' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
					<input type="hidden" name="code" value="<?php echo esc_attr( $euteajudo_request->post_name ); ?>">
					<input type="hidden" name="reply" value="<?php echo (int) $euteajudo_reply->comment_ID; ?>">
					<button class="eta-thanks" type="submit" aria-pressed="<?php echo $euteajudo_thanked ? 'true' : 'false'; ?>"><?php esc_html_e( 'This helped me', 'euteajudo' ); ?></button>
				</form>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>

	<div id="latest"></div>
	<?php if ( $euteajudo_closed ) : ?>
		<?php echo FormView::notice( __( 'This request is closed. You can reopen it whenever you want to keep talking.', 'euteajudo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
		<?php if ( $euteajudo_is_author ) : ?>
			<?php echo FormView::open( 'reopen' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
				<input type="hidden" name="code" value="<?php echo esc_attr( $euteajudo_request->post_name ); ?>">
				<button class="eta-button eta-button-quiet wp-element-button" type="submit"><?php esc_html_e( 'Reopen request', 'euteajudo' ); ?></button>
			</form>
		<?php endif; ?>
	<?php else : ?>
		<?php
		$euteajudo_last = end( $euteajudo_replies );
		if ( $euteajudo_is_author && ( false === $euteajudo_last || (int) $euteajudo_last->user_id === $euteajudo_viewer ) && 'sent' !== $euteajudo_step ) {
			echo '<p class="eta-note">' . esc_html__( 'Message sent. The reply may take a few hours.', 'euteajudo' ) . '</p>';
		}
		?>
		<?php echo FormView::open( 'reply' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
			<input type="hidden" name="code" value="<?php echo esc_attr( $euteajudo_request->post_name ); ?>">
			<label for="eta-reply-text"><?php esc_html_e( 'Reply', 'euteajudo' ); ?></label>
			<textarea id="eta-reply-text" name="text" rows="4" placeholder="<?php esc_attr_e( 'Write whenever you want', 'euteajudo' ); ?>"></textarea>
			<button class="eta-button wp-element-button" type="submit"><?php esc_html_e( 'Send reply', 'euteajudo' ); ?></button>
		</form>
		<?php if ( $euteajudo_is_author ) : ?>
			<details class="eta-close">
				<summary><?php esc_html_e( 'Close request', 'euteajudo' ); ?></summary>
				<?php echo FormView::open( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
					<input type="hidden" name="code" value="<?php echo esc_attr( $euteajudo_request->post_name ); ?>">
					<fieldset class="eta-before">
						<legend><?php esc_html_e( 'Why are you closing? (optional)', 'euteajudo' ); ?></legend>
						<label class="eta-choice"><input type="radio" name="reason" value="better"> <?php esc_html_e( 'I feel better', 'euteajudo' ); ?></label>
						<label class="eta-choice"><input type="radio" name="reason" value="no_more"> <?php esc_html_e( 'I do not want to receive more messages', 'euteajudo' ); ?></label>
						<label class="eta-choice"><input type="radio" name="reason" value="unspecified" checked> <?php esc_html_e( 'I prefer not to say', 'euteajudo' ); ?></label>
					</fieldset>
					<button class="eta-button eta-button-quiet wp-element-button" type="submit"><?php esc_html_e( 'Close request', 'euteajudo' ); ?></button>
				</form>
			</details>
		<?php endif; ?>
	<?php endif; ?>
	<?php echo View::disclosure(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in View. ?>
<?php endif; ?>
</div>
