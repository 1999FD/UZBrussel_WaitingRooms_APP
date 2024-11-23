$(document).ready(function() {       
    async function fetchData() {
        table = $('#theTable').DataTable();
        const fileListResponse = await fetch(`${baseUrl}/Custom_PHP/listWaitingRooms`);
        // Parse it
        const waitingRoomsFiles = await fileListResponse.json();
        console.log(waitingRoomsFiles);
        // Get waiting room dropdown
        const waiting_room_dropdown = document.getElementById('waiting_room_dropdown');
        waitingRoomsFiles.waitingroom_ids.forEach(waiting_room => {
            const option = document.createElement('option');
            option.value = waiting_room;
            option.text = waiting_room;
            waiting_room_dropdown.add(option);
        });

        // Select the dropdown value that corresponds to the current waiting room
        var currentWaitingRoomId;
        if (localStorage.getItem('waiting_room_id')) {
            currentWaitingRoomId = localStorage.getItem('waiting_room_id');
        }else {
            currentWaitingRoomId = waitingRoomsFiles.waitingroom_ids[0];
        }
        
        waiting_room_dropdown.value = currentWaitingRoomId;
        
        // Set event when option selected in dropdown
        waiting_room_dropdown.addEventListener('change', async function() {
            console.log("CHANGED")
            // Get selected waiting room id
            const waiting_room_id = this.value;
            // Set local storage waiting room id
            localStorage.setItem('waiting_room_id', waiting_room_id);
            // Get data from waiting room id
            const response = await fetch(`${baseUrl}/waiting_rooms_data.json?${Date.now()}`);
            const waiting_rooms_data = await response.json();  
            const waiting_room_data_displays = waiting_rooms_data[waiting_room_id];
            //For each in waiting room data
            table.clear().draw();
            if(waiting_room_data_displays != undefined) {
                Object.entries(waiting_room_data_displays).forEach(([key, value]) => {
                    // Sort the loketIDs
                    table.row.add([
                        key,
                        value.displayName,
                        value.orientationName,
                        `<div class="buttons"><button class="go-row">PREVIEW</button> <button class="delete-row">DELETE</button></div>` // Buttons side by side
                    ]).draw();
                });
            }
        });

        const response = await fetch(`${baseUrl}/Custom_PHP/listWaitingRooms`);
        const waiting_rooms_data = await response.json();
        // Get waiting room id from local storage or first waiting room id
        var waiting_room_id;
        if (localStorage.getItem('waiting_room_id')) {
            waiting_room_id = localStorage.getItem('waiting_room_id');
        } else {
            waiting_room_id = waiting_rooms_data.waitingroom_ids[0];
        }
        // Set local storage waiting room id
        // Get data from waiting room id
        const resp = await fetch(`${baseUrl}/waiting_rooms_data.json?${Date.now()}`);
        const waiting_rooms = await resp.json();  
        if(waiting_rooms[waiting_room_id] != undefined) {
            const waiting_room_data_displays = waiting_rooms[waiting_room_id];        
            Object.entries(waiting_room_data_displays).forEach(([key, value]) => {
                // Sort the loketIDs
                table.row.add([
                    key,
                    value.displayName,
                    value.orientationName,
                    `<div class="buttons"><button class="go-row">PREVIEW</button> <button class="delete-row">DELETE</button></div>` // Buttons side by side
                ]).draw();
            });
        }

        function myCallbackFunction (updatedCell, updatedRow, oldValue) {
            // Fetch json from url 
            displayId = updatedRow.data()[0];
            displayName = updatedRow.data()[1];
            orientationName = updatedRow.data()[2];
            wr_id = localStorage.getItem('waiting_room_id');

            // Send requst to update the data
            fetch(`${baseUrl}/Custom_PHP/uploadWZ`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    waiting_room_id: wr_id,
                    displayId: displayId,
                    displayName: displayName,
                    orientationName: orientationName
                }),
            })
            .then(data => {
                console.log(data);
            })
            console.log(wr_id, displayId, displayName, orientationName);
        }

        table.MakeCellsEditable({
            "onUpdate": myCallbackFunction,
            "columns": [1,2,3,4],
            "inputTypes": [
                {
                    column: 0,
                    type: "text",
                    options: null
                },
                {
                    column: 1,
                    type: "text",
                    options: null
                },
                {
                    column: 2,
                    type: "list",
                    options: [
                        { value: "horizontal", display: "horizontal" },
                        { value: "vertical", display: "vertical" },
                    ]
                }
            ]
        });
    }

    fetchData();
    // Handle Enter key press to trigger blur event
    $('#theTable').on('keydown', 'input, select', function(e) {
        if (e.key === 'Enter') {
            $(this).blur(); // Trigger blur event on Enter key press
        }
    });

    // Handle Enter key press to trigger blur event
    $('#theTable').on('keydown', 'select', function(e) {
        if (e.key === 'Enter') {
            $(this).blur(); // Trigger blur event on Enter key press
            $(this).updateEditableCell(this);
        }
    });

    // Handle delete row button click
    $('#theTable tbody').on('click', 'button.delete-row', function() {
        console.log("delete row");
        // table.row($(this).parents('tr')).remove().draw();
    });

    // Handle add row button click, add row with id the highest id + 1
    $('#add-row').on('click', function() {
        // Find the highest ID in the table
        // Take the service name of another table row
        let highestId = 0;
        table.rows().every(function() {
            const data = this.data();
            const id = parseInt(data[0], 10);
            if (id > highestId) {
                highestId = id;
            }
        });

        const newId = highestId + 1;
        // Take content the first playlist
        table.row.add([newId.toString(), 'Display Name', 'horizontal', `<div class="buttons"><button class="go-row">PREVIEW</button> <button class="delete-row">DELETE</button></div>`]).draw();
        // Also upload the new row to the server
        fetch(`${baseUrl}/Custom_PHP/uploadWZ`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                waiting_room_id: localStorage.getItem('waiting_room_id'),
                displayId: newId,
                displayName: 'Display Name',
                orientationName: 'horizontal'
            }),
        })
    });

    // Handle delete row button click and delete from server
    $('#theTable tbody').on('click', 'button.delete-row', function() {
        const row = table.row($(this).parents('tr'));
        const rowData = row.data();
        const id = rowData[0];
        row.remove().draw();
        // Delete from server

        fetch(`${baseUrl}/Custom_PHP/deleteWZ`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                // Get wachtzaal id from local storage
                waiting_room_id: localStorage.getItem('waiting_room_id'),
                displayId: id
            }),
        })

    });

    // Handle delete row button click and delete from server
    $('#theTable tbody').on('click', 'button.go-row', function() {
        console.log("GOOO")
        const row = table.row($(this).parents('tr'));
        const rowData = row.data();
        const id = rowData[0];
        const orientationName = rowData[2];
        // Get waiting room id from dropdown value
        const waitingRoomId = document.getElementById('waiting_room_dropdown').value;
        // Go to display page
        window.location.href = `${baseUrl}/html/displayWaitingRoom?waitingRoomId=${waitingRoomId}&displayId=${id}`;
    });

    // // Handle click on Loketten column to redirect to the playlist URL
    // $('#theTable tbody').on('click', 'td:nth-child(4)', function() {
    //     // Get the row associated with the clicked cell
    //     var row = table.row($(this).parents('tr')).data();
    //     const displayId = row[0];
    //     const displayName = row[1];
    //     const orientationName = row[2];
    //     console.log(displayId, displayName, orientationName);
    //     // Always pass selected dropdown value
    //     const dropdownvalue = document.getElementById('waiting_room_dropdown').value;
    //     // Redirect to another page with the loketIDs as query parameter
    //     window.location.href = `./waiting_rooms_display.html?&displayId=${displayId}&displayName=${displayName}&orientationName=${orientationName}&waitingRoomId=${dropdownvalue}`;
    // });
});