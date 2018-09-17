<h2>Attendee Information</h2>
<h3>Refreshment Options:</h3>
<p>
<label>
	<input class="w3-radio" name="beverageOptions" type="radio" value="alcoholic" <?php isset($beverageOptions) && $beverageOptions == "alcoholic" && print( "checked" )?> /> Alcoholic (incl. beer, cider, wine, etc.)</label>
	<br><label>
	<input class="w3-radio" name="beverageOptions" type="radio" value="non-alcoholic" <?php isset($beverageOptions) && $beverageOptions == "non-alcoholic" && print( "checked" )?> /> Children's/Non-alcoholic (incl. Tea, coffee, soft drink, etc.)</label>
</p>
	<h3>Meal Options:</h3>
<p>
<label>
	<input class="w3-radio" name="mealOptions" type="radio" value="adult" <?php isset($mealOptions) && $mealOptions == "adult" && print( "checked" )?> /> Adult's meal</label>
	<br><label>
	<input class="w3-radio" name="mealOptions" type="radio" value="child" <?php isset($mealOptions) && $mealOptions == "child" && print( "checked" )?> /> Under 16's meal</label>
</p>
	<h3>Dietary Requirements:</h3>
	<p>
<label>
	<input class="w3-radio" name="dietaryReqs" type="radio" value="" <?php isset($dietaryReqs) && $dietaryReqs == "" && print( "checked" )?> /> N/A</label>
	<br><label>
	<input class="w3-radio" name="dietaryReqs" type="radio" value="glutenfree" <?php isset($dietaryReqs) && $dietaryReqs == "glutenfree" && print( "checked" ) ?> /> Coeliac</label>
	<br><label>
	<input class="w3-radio" name="dietaryReqs" type="radio" value="vegetarian" <?php isset($dietaryReqs) && $dietaryReqs == "vegetarian" && print( "checked" )?> /> Vegetarian</label>
	<br><label>
	<input class="w3-radio" name="dietaryReqs" type="radio" value="vegan" <?php isset($dietaryReqs) && $dietaryReqs == "vegan" && print( "checked" )?> /> Vegan</label>
	<br><label>
	<input class="w3-radio" name="dietaryReqs" type="radio" value="other" <?php isset($dietaryReqs) && $dietaryReqs == "other" && print( "checked" )?> /> Other</label>
	<br><textarea class="w3-input w3-animate-input" style="width: 50%; height: 95px;" name="otherDietaryReqs" placeholder="Please describe any other dietary requirements so that we may pass them on for catering purposes"><?php isset($otherDietaryReqs) && print( htmlentities( $otherDietaryReqs ) )?></textarea>
	</p>