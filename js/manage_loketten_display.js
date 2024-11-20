function updateDisplay (displayId, locationName, orientationName, content, loketIDs) {
    // Fetch json from url 
    displayId = displayId;
    locationName = locationName;
    orientationName = orientationName;
    content = content;
    loketIDs = loketIDs
    console.log(loketIDs)
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

$(document).ready(async function() {
    const urlParams = new URLSearchParams(window.location.search);
    console.log(urlParams);
    const loketIDs = urlParams.get('loketIDs');
    console.log(loketIDs);
    const displayId = urlParams.get('displayId');
    const locationName = urlParams.get('locationName');
    const orientationName = urlParams.get('orientationName');
    const content = urlParams.get('content');

    if (!loketIDs) {
        console.error('loketIDs not found in query parameters.');
        return;
    }

    // Parse loketIDs into an array (assuming the loketIDs are comma-separated in the query params)
    const selectedLoketIDs = loketIDs.split(',');

    try {
        // Fetch the XML file
        const response = await fetch(`${baseUrl}/Shares/TicketQueues.xml?${Date.now()}`);
        const xmlText = await response.text();
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlText, 'text/xml');
        
        // Convert to JSON
        const loketten = JSON.parse(xml2json(xmlDoc, "")).TicketQueues.TicketQueue;

        // Separate selected loketten from non-selected
        const selectedLoketten = loketten.filter(loket => selectedLoketIDs.includes(loket.id));
        const otherLoketten = loketten.filter(loket => !selectedLoketIDs.includes(loket.id));

        // Combine selected and non-selected loketten
        const sortedLoketten = [...selectedLoketten, ...otherLoketten];

        // Build the table dynamically
        const tbody = $('tbody');
        tbody.empty();

        sortedLoketten.forEach(loket => {
            const isChecked = selectedLoketIDs.includes(loket.id) ? 'checked' : '';
            const row = `
                <tr>
                    <td class="checkbox-column">
                        <input type="checkbox" id="item${loket.id}" data-loket-id="${loket.id}" ${isChecked}>
                    </td>
                    <td>${loket.id}</td>
                    <td>${loket.name}</td>
                    <td>${loket.service}</td>
                </tr>
            `;
            tbody.append(row);
        });

        // Initialize DataTables for sorting and filtering
        const table = $('#loketTable').DataTable({
            "order": [],  // Disable initial ordering
            "paging": true,  // Disable pagination
            "pageLength": 15,  // Set default number of entries to show per page
            "lengthMenu": [10, 15, 30, 50],  // Options for entries per page
        });

        // Custom search functionality with the search input
        $('#searchBox').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Add a listener for checkbox clicks
        $('tbody').on('change', 'input[type="checkbox"]', function() {
            const loketId = $(this).data('loket-id');
            const isChecked = $(this).is(':checked');

            // Perform your desired action here
            if (isChecked) {
                console.log(`Loket ${loketId} is checked.`);
                // Add loketId to the selectedLoketIDs array
                selectedLoketIDs.push(loketId.toString());
                updateDisplay(displayId, locationName, orientationName, content, selectedLoketIDs);
            } else {
                console.log(`Loket ${loketId} is unchecked.`);
                // Remove loketId from the selectedLoketIDs array
                const index = selectedLoketIDs.indexOf(loketId.toString());
                if (index > -1) {
                    selectedLoketIDs.splice(index, 1);
                }
                updateDisplay(displayId, locationName, orientationName, content, selectedLoketIDs);
            }
        });

        // Click on confirm button should go back
        $('#btnConfirm').on('click', function() {
            window.location.href = `${baseUrl}/html/manage_loketten`;
        });

    } catch (error) {
        console.error("Error fetching or parsing the XML: ", error);
    }
});
