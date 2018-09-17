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
	<form method="POST" name="attendeeSelection">
		<h2>Please select the person you wish to respond on behalf of</h2>

	<ul class="w3-ul w3-card-4">
	<?php
	foreach ( $associatedAttendees as $attendee )
	{ 
		$attendingText = 'no response yet';
		$iconClass = 'fa-user-secret w3-light-grey';
		if( $attendee->attendance === '1' )
		{
			$attendingText = 'attending';
			$iconClass = 'fa-user-plus w3-pale-green';
		}
		else if( $attendee->attendance === '0' )
		{
			$attendingText = 'not attending';
			$iconClass = 'fa-user-minus w3-pale-red';
		}
		?>
		<li class="w3-bar">
			<div class="w3-bar-item">
				<i class="fa fa-3x <?php print( $iconClass ); ?>" style="width: 100px;"></i>
			</div>
			<div class="w3-bar-item">
				<span class="w3-xlarge"><?php print( $attendee->firstName . ' ' . $attendee->lastName ); ?></span><br>
				<span><?php print( $attendingText ); ?></span>
			</div>
			<div class="w3-bar-item w3-right">
				<button class="w3-button w3-medium w3-white w3-border w3-green-border" onclick="applyAuthCtx('<?php print( htmlentities( $attendee->authCtx ) ); ?>');">Review attendance</button>
				<?php 
				if( !false )
				{ ?>
				<button class="w3-button w3-medium w3-white w3-border w3-green-border" onclick="applyAuthCtx('<?php print( htmlentities( $attendee->authCtx ) ); ?>');applyAction('validate-apply-contact-details')">Apply my contact details</button><?php
				} ?>
			</div>
		</li>
	<?php
	} ?>
	</ul>

	<p>
		<button class="w3-button w3-large w3-blue" onclick="applyAction('validate-alldone-party')" >All done!</button>
	</p>
		<input type="hidden" name="action" value="validate-partyselect" />
		<input type="hidden" name="authCtx" value="<?php print( htmlentities( $authCtx ) ); ?>" />
		<!-- Mathew, fuck off. I know that this isn't very secure! -->
		<input type="hidden" name="accessToken" value="<?php print( htmlentities( $accessToken ) ); ?>" />

		<?php wp_nonce_field( $nonceAction, $nonceName );
	?>
	</form>
	<script>
	var applyAuthCtx = ( ctx ) => {
		document.querySelector('input[name=authCtx]').value = ctx;
	}
	var applyAction = ( action ) => {
		document.querySelector('input[name=action]').value = action;
	}
	</script>
</div>
