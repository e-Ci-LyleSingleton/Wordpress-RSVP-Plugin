<?php
foreach ( $errors as $key => $value )
{ ?>
    <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
        <p>
            <?php print( $value ); ?>
        </p>
    </div>
<?php
} ?>

<div class="container">
<form method="POST">
<h2>Enter your name</h2>

<label>First name: <input class="w3-input" name="firstName" type="text" placeholder="First Name" value="<?php isset($firstName) && print( $firstName )?>" required/>
</label>
<label>Last name: <input class="w3-input" name="lastName" type="text" placeholder="Last Name" value="<?php isset($lastName) && print( $lastName )?>" required />
</label>
<p>
    <input class="w3-button w3-large w3-blue" type="submit" value="Find my RSVP"/>
</p>
    <input type="hidden" name="action" value="validate-authorise"/>

	<?php wp_nonce_field( $nonceAction, $nonceName );?>
</form>
</div>
