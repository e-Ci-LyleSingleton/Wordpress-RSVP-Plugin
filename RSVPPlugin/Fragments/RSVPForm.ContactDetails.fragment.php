	<h2>Contact Details</h2>
<p>
	<label>Email:<br>
		<input class="w3-input" name="email" type="email" placeholder="E-Mail Address" value="<?php isset($email) && print( $email )?>"  /><br>
	</label>
	<label>Contact Number:<br>
		<input class="w3-input" name="phone" type="tel" placeholder="Contact Phone Number" value="<?php isset($phone) && print( $phone )?>" /><br>
	</label>
	<label>Street Address:<br>
		<input class="w3-input" name="street" type="text" placeholder="Street Address" value="<?php isset($street) && print( $street )?>" /><br>
	</label>
	<label>City:<br>
		<input class="w3-input" name="city" type="text" placeholder="City" value="<?php isset($city) && print( $city )?>" /><br>
	</label>
	<label>Postcode:<br>
		<input class="w3-input" name="postcode" type="number" placeholder="Postcode" value="<?php isset($postcode) && print( $postcode )?>" />
	</label>
</p>