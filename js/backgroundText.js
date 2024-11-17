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
        const backgroundText = document.querySelector('textarea[name="backgroundText"]');
        backgroundText.value = jsonData.backgroundText;
    }

    checkUploadSuccessParameter();
    fetchData();
});