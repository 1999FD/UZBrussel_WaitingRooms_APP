$(document).ready(function() {
    function checkUploadSuccessParameter(){
        // Check if uploadSuccess parameter is present in the URL
        var url = new URL(window.location.href);
        if (url.searchParams.has('uploadSuccess')) {
            // Show alert if uploadSuccess is true
            alert("File uploaded successfully!");

            // Remove all occurrences of uploadSuccess parameter from the URL
            url.searchParams.delete('uploadSuccess');

            // Update the URL without refreshing the page
            window.history.replaceState(null, null, url.href);
        }
    }

    async function fetchData() {
        const response = await fetch(`${baseUrl}/data.json?${Date.now()}`);
        const jsonData = await response.json();
        // Fill text area from backgroundText with
        const displays = jsonData.displays;
        const fileSelect = document.getElementById('fileSelect');
        Object.entries(displays).forEach(([key, value]) => {
            const locationName = value.locationName;
            const option = document.createElement('option');
            option.value = key;
            option.text = locationName;
            fileSelect.appendChild(option);    
        });
        // When fileSelect selection is made
        fileSelect.addEventListener('change', () => {
            const selectedFile = fileSelect.options[fileSelect.selectedIndex].value;
            console.log(selectedFile)
            const selectedOrientation = displays[selectedFile].orientation;
            // Check the orientation of the selected file
            if (selectedOrientation === 'horizontal') {
                document.getElementById('horizontal').checked = true;
            } else {
                document.getElementById('vertical').checked = true;
            }
        });

    }

    checkUploadSuccessParameter();
    fetchData();
});