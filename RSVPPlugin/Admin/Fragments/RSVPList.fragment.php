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

<div class="w3-container">
    <h2>Response pending</h2>
    <div class="w3-card w3-margin">
        <table class="w3-table-all" id="awaiting">
            <thead>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Dietary Requirements</th>
                <th>Phone</th>
                <th>Email</th>
            </thead>
            <tbody><?php
                foreach ( $awaiting as $attendee )
                { ?>
                    <tr>
                        <td><?php print( htmlentities( $attendee->firstName ) ); ?></td>
                        <td><?php print( htmlentities( $attendee->lastName ) ); ?></td>
                        <td><span class="w3-tooltip"><?php print( htmlentities( $attendee->dietaryReqs ) ); ?>
                            <span class="w3-text">(<?php print( htmlentities( $attendee->otherDietaryReqs ) ); ?>)</span>
                        </span>
                        </td>
                        <td><a href="tel:<?php print( htmlentities( $attendee->phone ) ); ?>"><?php print( htmlentities( $attendee->phone ) ); ?></a></td>
                        <td><a href="mailto:<?php print( htmlentities( $attendee->email ) ); ?>"><?php print( htmlentities( $attendee->email ) ); ?></a></td>
                    </tr><?php
                } ?>
            </tbody>
        </table>
        <button id="pendingExport" type="button" class="w3-button w3-green w3-margin">Export CSV</button>
    </div>
    <h2>Attending</h2>
    <div class="w3-card w3-margin">
        <table class="w3-table-all" id="attending">
            <thead>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Dietary Requirements</th>
                <th>Phone</th>
                <th>Email</th>
            </thead>
            <tbody><?php
                foreach ( $attendees as $attendee )
                { ?>
                    <tr>
                        <td><?php print( htmlentities( $attendee->firstName ) ); ?></td>
                        <td><?php print( htmlentities( $attendee->lastName ) ); ?></td>
                        <td><span class="w3-tooltip"><?php print( htmlentities( $attendee->dietaryReqs ) ); ?>
                            <span class="w3-text">(<?php print( htmlentities( $attendee->otherDietaryReqs ) ); ?>)</span>
                        </span>
                        </td>
                        <td><a href="tel:<?php print( htmlentities( $attendee->phone ) ); ?>"><?php print( htmlentities( $attendee->phone ) ); ?></a></td>
                        <td><a href="mailto:<?php print( htmlentities( $attendee->email ) ); ?>"><?php print( htmlentities( $attendee->email ) ); ?></a></td>
                    </tr><?php
                } ?>
            </tbody>
        </table>
        <button id="attendingExport" type="button" class="w3-button w3-green w3-margin">Export CSV</button>
    </div>
    <h2>Not attending</h2>
    <div class="w3-card w3-margin">
        <table class="w3-table-all" id="notAttending">
            <thead>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Dietary Requirements</th>
                <th>Phone</th>
                <th>Email</th>
            </thead>
            <tbody><?php
                foreach ( $absentees as $attendee )
                { ?>
                    <tr>
                        <td><?php print( htmlentities( $attendee->firstName ) ); ?></td>
                        <td><?php print( htmlentities( $attendee->lastName ) ); ?></td>
                        <td><span class="w3-tooltip" title="<?php print( htmlentities( $attendee->otherDietaryReqs ) ); ?>">
                        <?php print( htmlentities( $attendee->dietaryReqs ) ); ?>
                            <span class="w3-text">
                                (<?php print( htmlentities( $attendee->otherDietaryReqs ) ); ?>)
                            </span>
                        </span>
                        </td>
                        <td><a href="tel:<?php print( htmlentities( $attendee->phone ) ); ?>"><?php print( htmlentities( $attendee->phone ) ); ?></a></td>
                        <td><a href="mailto:<?php print( htmlentities( $attendee->email ) ); ?>"><?php print( htmlentities( $attendee->email ) ); ?></a></td>
                    </tr><?php
                } ?>
            </tbody>
        </table>
        <button id="notAttendingExport" type="button" class="w3-button w3-green w3-margin">Export CSV</button>
    </div>
</div>
<script>
var attendingTable = document.querySelector("#attending");
var awaitingTable = document.querySelector("#awaiting");
var notAttendingTable = document.querySelector("#notAttending");
var attendingDataset = null;
var awaitingDataset = null;
var notAttendingDataset = null;

document.querySelector('#attendingExport').addEventListener( 'click',
    ( event ) =>
    {
        var exportDate = new Date();
		var data = {
			type: 'csv',
			filename: `attendingGuests_${exportDate.getYear()}-${exportDate.getMonth()+1}-${exportDate.getDay()}_${exportDate.getHours()}-${exportDate.getMinutes()}-${exportDate.getSeconds()}`,
			columnDelimiter: ','
		}
		attendingDataset.export(data);
    } );
document.querySelector('#pendingExport').addEventListener( 'click',
( event ) =>
{
    var exportDate = new Date();
    var data = {
        type: 'csv',
        filename: `pendingGuests_${exportDate.getYear()}-${exportDate.getMonth()+1}-${exportDate.getDay()}_${exportDate.getHours()}-${exportDate.getMinutes()}-${exportDate.getSeconds()}`,
        columnDelimiter: ','
    }
    awaitingDataset.export(data);
} );
document.querySelector('#notAttendingExport').addEventListener( 'click',
( event ) =>
{
    var exportDate = new Date();
    var data = {
        type: 'csv',
        filename: `notAttendingGuests_${exportDate.getYear()}-${exportDate.getMonth()+1}-${exportDate.getDay()}_${exportDate.getHours()}-${exportDate.getMinutes()}-${exportDate.getSeconds()}`,
        columnDelimiter: ','
    }
    notAttendingDataset.export(data);
} );

window.addEventListener('load',
    ( event ) => {
        attendingDataset = new DataTable(attendingTable);
        awaitingDataset = new DataTable(awaitingTable);
        notAttendingDataset = new DataTable(notAttendingTable);
});

</script>
