<h2>Other Information:</h2>
<p><label> Song Request:<br>
	<input class="w3-input" type="text" name="songRequest" placeholder="Requested Song"  value="<?php isset($songRequest) && print( htmlentities( $songRequest ) )?>" /></label>
<label> Notes:<br>
		<textarea class="w3-input w3-animate-input" style="width: 50%; height: 95px;" name="attendanceNotes" placeholder="Please leave any notes regarding your attendance here. e.g. You will only be able to attend the ceremony."><?php isset($attendanceNotes) && print( htmlentities( $attendanceNotes ) )?></textarea></label><p>
		