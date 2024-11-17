$(document).ready(function() {    
    console.log("test")
    var playlists;
    async function fetchData() {
        table = $('#theTable').DataTable();
        const response = await fetch(`${baseUrl}/data.json?${Date.now()}`);
        const displays = await response.json();
        const responseContent = await fetch("https://signage.iddprojects.be/api/playlists-idd-iptv/24");
        // const responseContent = await fetch(`${baseUrl}/data2.json?${Date.now()}`);
        const departments = await responseContent.json();
        const departmentsArray = Object.values(departments.departments);
        playlists = departmentsArray.find((item) => item.department_name === "UZB_Loketten").playlists;
        // Add object
        playlists.push({name: 'No Playlist', url: ''});
        // Get from json object where department_name equal to 'Test_it'

        Object.entries(displays).forEach(([key, value]) => {
            // Sort the loketIDs
            value.loketIDs.sort((a, b) => a - b);
            table.row.add([
                value.displayId,
                value.locationName,
                value.orientationName,
                value.content,
                value.loketIDs.join(', '),
                `<div class="buttons"><button class="go-row">PREVIEW</button> <button class="delete-row">DELETE</button></div>` // Buttons side by side
            ]).draw();
        });

        function myCallbackFunction (updatedCell, updatedRow, oldValue) {
            // Fetch json from url 
            displayId = updatedRow.data()[0];
            locationName = updatedRow.data()[1];
            orientationName = updatedRow.data()[2];
            content = updatedRow.data()[3];
            //Sort the loketIDs
            loketIDs = updatedRow.data()[4].split(',').map(Number).sort((a, b) => a - b);

            // Send requst to update the data
            fetch(`${baseUrl}/Custom_PHP/upload`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    displayId: displayId,
                    locationName: locationName,
                    orientationName: orientationName,
                    content: content,
                    loketIDs: loketIDs,
                }),
            })
            .then(data => {
                console.log('Success:', data);
            })
            console.log(displayId, locationName, orientationName, content, loketIDs);
        }

        table.MakeCellsEditable({
            "onUpdate": myCallbackFunction,
            "columns": [1,2,3,4],
            "inputTypes": [
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
                },
                {
                    column: 3,
                    type: "list",
                    options: playlists.map(item => ({ value: item.name, display: item.name }))
                }
            ]
        });

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
            let highestId = -1;
            table.rows().every(function() {
                const data = this.data();
                const id = parseInt(data[0], 10);
                if (id > highestId) {
                    highestId = id;
                }
            });
            console.log(highestId);
            const newId = highestId + 1;
            // Take content the first playlist
            const content = playlists[0].name;
            table.row.add([newId.toString(), 'LocationName', 'horizontal', content, "1", `<div class="buttons"><button class="go-row">PREVIEW</button> <button class="delete-row">DELETE</button></div>`]).draw();
            // Also save it
            fetch(`${baseUrl}/PHP/upload`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    displayId: newId,
                    locationName: 'LocationName',
                    orientationName: 'horizontal',
                    content: content,
                    loketIDs: ["1"],
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

            fetch(`${baseUrl}/php/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    displayId: id
                }),
            })

        });

        // Handle delete row button click and delete from server
        $('#theTable tbody').on('click', 'button.go-row', function() {
            const row = table.row($(this).parents('tr'));
            const rowData = row.data();
            const id = rowData[0];
            // Go to display page
            window.location.href = `${baseUrl}/html/display?id=${id}`;
        });

        // Handle click on Loketten column to redirect to the playlist URL
        $('#theTable tbody').on('click', 'td:nth-child(5)', function() {
            console.log("CLICKED");
            // Get the row associated with the clicked cell
            var row = table.row($(this).parents('tr')).data();
            // Get the loketIDs
            const loketIDs = row[4].split(',').map(Number);
            // Log the entire row element to inspect
            console.log(loketIDs);

            // Redirect to another page with the loketIDs as query parameter
            window.location.href = `./manage_loketten_display.html?loketIDs=${loketIDs}&displayId=${row[0]}&locationName=${row[1]}&orientationName=${row[2]}&content=${row[3]}`;
        });
    }

    fetchData();
});