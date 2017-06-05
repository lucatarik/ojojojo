<!DOCTYPE html>
<html>
    <head>
        <title>Goog</title>
        <meta charset='utf-8' />
        <script src="//code.jquery.com/jquery-3.2.1.js"></script>

    </head>
    <body>
        <p>Google Sheets API Quickstart</p>

        <!--Add buttons to initiate auth sequence and sign out-->
        <button id="authorize-button" style="display: none;">Authorize</button>
        <button id="signout-button" style="display: none;">Sign Out</button>

        <pre id="content"></pre>

        <script type="text/javascript">
            // Client ID and API key from the Developer Console
            var CLIENT_ID = '857416814607-pv9fjh0mtblrcounjd5qrm9sdg3tle6m.apps.googleusercontent.com';

            // Array of API discovery doc URLs for APIs used by the quickstart
            var DISCOVERY_DOCS = ["https://sheets.googleapis.com/$discovery/rest?version=v4", "https://www.googleapis.com/discovery/v1/apis/drive/v3/rest"];

            // Authorization scopes required by the API; multiple scopes can be
            // included, separated by spaces.
            var SCOPES = "https://www.googleapis.com/auth/spreadsheets.readonly https://www.googleapis.com/auth/drive.metadata.readonly";

            var authorizeButton = document.getElementById('authorize-button');
            var signoutButton = document.getElementById('signout-button');

            /**
             *  On load, called to load the auth2 library and API client library.
             */
            function handleClientLoad() {
                gapi.load('client:auth2', initClient);
            }

            /**
             *  Initializes the API client library and sets up sign-in state
             *  listeners.
             */
            function initClient() {
                gapi.client.init({
                    discoveryDocs: DISCOVERY_DOCS,
                    clientId: CLIENT_ID,
                    scope: SCOPES
                }).then(function () {
                    $.getJSON('gSheet.php').then(function (data)
                    {
                        console.log(data);
                        if (data.hasOwnProperty("access_token"))
                        {
                            gapi.auth.setToken(data);
                            gapi.auth2.getAuthInstance().isSignedIn.set(true);
                        }
                        // Listen for sign-in state changes.
                        gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

                        // Handle the initial sign-in state.
                        updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
                        authorizeButton.onclick = handleAuthClick;
                        signoutButton.onclick = handleSignoutClick;
                    })

                });
            }

            /**
             *  Called when the signed in status changes, to update the UI
             *  appropriately. After a sign-in, the API is called.
             */
            function updateSigninStatus(isSignedIn) {
                if (isSignedIn) {
                    listFiles();
                } else {
                    authorizeButton.style.display = 'block';
                    signoutButton.style.display = 'none';
                }
            }

            /**
             *  Sign in the user upon button click.
             */
            function handleAuthClick(event) {
                //gapi.auth2.getAuthInstance().signIn();
                gapi.auth2.getAuthInstance().grantOfflineAccess().then(signInCallback);

            }

            /**
             *  Sign out the user upon button click.
             */
            function handleSignoutClick(event) {
                gapi.auth2.getAuthInstance().signOut();
            }

            /**
             * Append a pre element to the body containing the given message
             * as its text node. Used to display the results of the API call.
             *
             * @param {string} message Text to be placed in pre element.
             */
            function appendPre(message) {
                var pre = document.getElementById('content');
                $(pre).append(message + "<br>");
            }

            /**
             * Print the names and majors of students in a sample spreadsheet:
             * https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
             */
            function getSpridShit(id) {
                gapi.client.sheets.spreadsheets.values.get({
                    spreadsheetId: id,
                    range: "A1:Z999",
                    "majorDimension": "COLUMNS",
                }).then(function (response) {
                    var range = response.result;
                    ress = response;

                    if (range.values.length > 0) {
                        for (var i = 0; i < range.values.length; i++) {
                            var row = range.values[i];
                            // Print columns A and E, which correspond to indices 0 and 4.
                            appendPre(row.join(', '));
                        }
                    } else {
                        appendPre('No data found.');
                    }
                }, function (response) {
                    appendPre('Error: ' + response.result.error.message);
                });
            }

            function signInCallback(authResult) {

                if (authResult['code']) {

                    // Hide the sign-in button now that the user is authorized, for example:
                    $('#signinButton').attr('style', 'display: none');

                    // Send the code to the server
                    $.post("gSheet.php", {"code": authResult['code']});
                } else {
                    // There was an error.
                }
            }

            /**
             * Print files.
             */
            function listFiles() {
                gapi.client.drive.files.list({
                    'pageSize': 10,
                    'fields': "nextPageToken, files(id, name)",
                    'q': "mimeType='application/vnd.google-apps.spreadsheet'"
                }).then(function (response) {
                    appendPre('Files:');
                    var files = response.result.files;
                    if (files && files.length > 0) {
                        for (var i = 0; i < files.length; i++) {
                            var file = files[i];
                            appendPre('<span class="openFile" data-id="' + file.id + '">' + file.name + ' (' + file.id + ')</span>');
                        }
                        $('.openFile').unbind('click').bind("click", function ()
                        {
                            getSpridShit($(this).data('id'));
                        });
                    } else {
                        appendPre('No files found.');
                    }
                });
            }



            // function load the calendar api and make the api call
            function makeApiCall(today) {
                var resource = {
                    "summary": "Sample Event " + Math.floor((Math.random() * 10) + 1),
                    "start": {
                        "dateTime": today
                    },
                    "end": {
                        "dateTime": today
                    }
                };
                gapi.client.calendar.events.insert({
                    'calendarId': 'primary', // calendar ID
                    "resource": resource							// pass event details with api call
                }).then(function (resp) {
                    if (resp.status == 'confirmed') {
                        appendPre("Event created successfully. View it <a href='" + resp.htmlLink + "'>online here</a>.");
                    } else {
                        appendPre("There was a problem. Reload page and try again.");
                    }
                    /* for (var i = 0; i < resp.items.length; i++) {		// loop through events and write them out to a list
                     var li = document.createElement('li');
                     var eventInfo = resp.items[i].summary + ' ' +resp.items[i].start.dateTime;
                     li.appendChild(document.createTextNode(eventInfo));
                     document.getElementById('events').appendChild(li);
                     } */
                    console.log(resp);
                });
            }


        </script>

        <script async defer src="https://apis.google.com/js/api.js"
                onload="this.onload = function () {
                };
                handleClientLoad()"
                onreadystatechange="if (this.readyState === 'complete') this.onload()">
        </script>
    </body>
</html>