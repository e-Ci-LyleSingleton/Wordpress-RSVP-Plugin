<?php
foreach ( $errors as $key => $value )
{ ?>
    <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
        <p>
            <?php print( htmlentities( $value ) ); ?>
        </p>
    </div>
<?php
} ?>

<div class="w3-container">
    <?php
    if( $attendance == '1' )
    {
        ?><h1>It&apos;s time nearly time to celebrate, <?php print( htmlentities( $firstName ) ); ?>!</h1><?php
    }
    else 
    {
        ?><h1>It&apos;s a shame that you can't make it, <?php print( htmlentities( $firstName ) ); ?>!</h1>
        <p>There's still time to free your schedule!</p><?php
    } ?>
    <p>Here is a summary of what you have told us:</p>
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
        </li><?php
    } ?>
    </ul>
    <p>There will be lots of fun things to see and do and you will be surrounded by great people just like you!</p>
</div>
