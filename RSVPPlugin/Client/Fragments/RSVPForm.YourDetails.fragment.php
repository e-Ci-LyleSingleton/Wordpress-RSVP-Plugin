<h2>Your Details</h2>
<p><label>First Name:<br>
	<input class="w3-input" name="firstName" type="text" placeholder="First Name" value="<?php isset($firstName) && print( htmlentities( $firstName ) )?>" required/><br>
</label>
<label>Last Name:<br>
	<input class="w3-input" name="lastName" type="text" placeholder="Last Name" value="<?php isset($lastName) && print( htmlentities( $lastName ) )?>" required /><br>
</label></p>

<h3>Attendance</h3>
<p><label>
	<input class="w3-radio" name="attendance" type="radio" value="1" <?php isset($attendance) && $attendance === "1" && print( "checked" )?> required /> Attending</label><br>
<label>
	<input class="w3-radio" name="attendance" type="radio" value="0" <?php isset($attendance) && $attendance === "0" && print( "checked" )?> /> Not Attending</label>
	</p>