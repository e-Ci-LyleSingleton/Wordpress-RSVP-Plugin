<?php
foreach ( $errors as $key => $value )
{ ?>
    <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
        <p>
            <?php print( htmlentities( $value ) ); ?>
        </p>
    </div>
<?php
}
foreach ( $successes as $key => $value )
{ ?>
    <div class="w3-panel w3-pale-green w3-leftbar w3-border-green">
        <p>
            <?php print( htmlentities( $value ) ); ?>
        </p>
    </div>
<?php
} ?>
  <div class="container">
    <?php
    if( $attendance == '1' )
    {
        ?><h1>It&apos;s time nearly time to celebrate, <?php print( htmlentities( $firstName ) ); ?>!</h1>
        <p>There will be lots of fun things to see and do and you will be surrounded by great people just like you!</p><?php
    
    }
    else 
    {
        ?><h1>It&apos;s a shame that you can't make it, <?php print( htmlentities( $firstName ) ); ?>!</h1>
        <p>There will be lots of fun things to see and do and you would have been surrounded by great people just like you! There's still time to free your schedule!</p><?php
    } ?>
</div>