function updateDisplay(displayId, displayName, orientationName, serviceIds) {
    fetch(`${baseUrl}/Custom_PHP/uploadWZ`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            waiting_room_id: localStorage.getItem('waiting_room_id'),
            displayId: displayId,
            displayName: displayName,
            orientationName: orientationName,
            service: serviceIds,
        }),
    })
    .then(response => response.json())
    .then(data => console.log('Success:', data))
    .catch(error => console.error('Error updating display:', error));
}

$(document).ready(async function() {
    const urlParams = new URLSearchParams(window.location.search);
    const serviceIds = urlParams.get('serviceIds');
    const displayId = urlParams.get('displayId');
    const displayName = urlParams.get('displayName');
    const orientationName = urlParams.get('orientationName');
    const waitingRoomId = urlParams.get('waitingRoomId');

    // Parse serviceIds into an array, handling empty or null values
    let selectedServices = serviceIds ? serviceIds.split(',').filter(id => id) : [];

    try {
        // Fetch and parse XML file
        const XMLFileName = `Waitingroom_${waitingRoomId}.xml`;
        const response = await fetch(`${baseUrl}/Shares/AFROEPNUMMERS/${XMLFileName}?${Date.now()}`);
        const xmlText = await response.text();
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlText, 'text/xml');
        const services = JSON.parse(xml2json(xmlDoc, "")).data.Waitingroom.Services.Service;

        const tbody = $('tbody');
        tbody.empty();

        services.forEach(service => {
            const isChecked = selectedServices.includes(service.Id) ? 'checked' : '';
            const row = `
                <tr>
                    <td class="checkbox-column">
                        <input type="checkbox" id="item${service.Id}" data-service-id="${service.Id}" ${isChecked}>
                    </td>
                    <td>${service.Id}</td>
                    <td>${service.NameNL}</td>
                </tr>
            `;
            tbody.append(row);
        });

        const table = $('#loketTable').DataTable({
            "order": [],  // Disable initial ordering
            "paging": true,
            "pageLength": 15,
            "lengthMenu": [10, 15, 30, 50],
        });

        $('#searchBox').on('keyup', function() {
            table.search(this.value).draw();
        });

        $('tbody').on('change', 'input[type="checkbox"]', function() {
            const serviceId = $(this).data('service-id');
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                if (!selectedServices.includes(serviceId)) {
                    selectedServices.push(serviceId);
                }
            } else {
                selectedServices = selectedServices.filter(id => id !== serviceId);
            }

            updateDisplay(displayId, displayName, orientationName, selectedServices);
        });

        $('#btnConfirm').on('click', function() {
            window.location.href = `${baseUrl}/html/waiting_rooms`;
        });

    } catch (error) {
        console.error("Error fetching or parsing the XML: ", error);
    }
});
