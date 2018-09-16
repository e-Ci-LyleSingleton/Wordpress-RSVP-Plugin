<?php
foreach ( $errors as $key => $value )
{ ?>
    <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
        <p>
            <?php print( $value ); ?>
        </p>
    </div>
<?php
}
foreach ( $successes as $key => $value )
{ ?>
    <div class="w3-panel w3-pale-green w3-leftbar w3-border-green">
        <p>
            <?php print( $value ); ?>
        </p>
    </div>
<?php
} ?>
<div class="container">
	<form method="POST" name="rsvpForm">

	<?php
	include("RSVPForm.YourDetails.fragment.php");
	include("RSVPForm.ContactDetails.fragment.php");
	include("RSVPForm.AttendeeInformation.fragment.php");
	include("RSVPForm.OtherInformation.fragment.php");
	?>
		<input class="w3-button w3-large w3-blue" type="submit" value="Please indicate you attendance"/>
		<input type="hidden" name="action" value="validate-attend" />
		<!-- Mathew, fuck off. I know that this isn't very secure! -->
		<input type="hidden" name="accessToken" value="<?php print( $accessToken ); ?>" />
		<input type="hidden" name="authCtx" value="<?php print( $authCtx ); ?>" />

		<?php wp_nonce_field( $nonceAction, $nonceName );
	?>
	</form>
</div>
<script>
var updateSubmitButtonText = ( isAttending ) => {
	var submitBtn = document.querySelector('input[type=submit]');
	submitBtn.disabled = false;
	if( isAttending === true )
	{
		submitBtn.value = 'Confirm your attendance';
	} 
	else if( isAttending === false )
	{
		submitBtn.value = 'Confirm your absence';
	}
	else
	{
		submitBtn.value = 'Please indicate you attendance';
		submitBtn.disabled = true;
	}
};

document.querySelectorAll('input[name=attendance]').forEach(radioEl => {

	radioEl.addEventListener( 'change',
		( event ) => {
			if( event.target.value == '1' )
			{
				updateSubmitButtonText( true );
			}
			if( event.target.value == '0' )
			{
				updateSubmitButtonText( false );
			}
		} );
});

window.addEventListener( 'load',
	( event ) => {
		var radioElList = document.querySelectorAll('input[name=attendance]:checked')
		if( radioElList.length == 1 )
		{
			updateSubmitButtonText( radioElList[0].value === '1'? true : false );
		}
		else
		{
			updateSubmitButtonText( null );
		}
	} );
</script>
