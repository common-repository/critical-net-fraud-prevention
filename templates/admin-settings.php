<!-- CONTENT -->
<div class="row" style="padding-top:15px">
	<?php
	/**
	 * Admin settings page.
	 *
	 * @package critical-net-fraud-prevention
	 */

	if ( isset( $_SESSION['cnfp_licence_key_saved'] ) ) {
		if ( 'success' === $_SESSION['cnfp_licence_key_saved'] ) {?>
			<div class="notice notice-success">
				<?php echo esc_html( $_SESSION['cnfp_session_message'] ); ?>
			</div>
			<?php
		} elseif ( 'error' === $_SESSION['cnfp_licence_key_saved'] ) {
			?>
			<div class="notice notice-error">
				<?php echo esc_html( $_SESSION['cnfp_session_message'] ); ?>
			</div>
			<?php
		}
		unset( $_SESSION['cnfp_licence_key_saved'] );
		unset( $_SESSION['cnfp_session_message'] );
	}
	?>

	<div class="container">
		<h1>CNFP Settings</h1>
		<form method="POST" action="">
			<div class="row p-3">
				<div class="col-md-6">
					<?php wp_nonce_field( 'set-license-key' ); ?>
					<label class="form-label">Licence Key</label>
					<div class="form-group mb-3">
						<input class="form-control" name="cnfp_licence_key" type="text" placeholder="Enter Licence Key" value="<?php echo esc_attr( get_option( 'cnfp_licence_key' ) ); ?>">
					</div>
					<button class="btn btn-primary">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>
