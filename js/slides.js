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

    async function fetchImages(orientation) {
        // Clear gallery
        $('.gallery').empty();
        const response = await fetch(`${baseUrl}/php/listImages`);
        const data = await response.json();
        const imagePaths = data[orientation]
        const gallery = document.querySelector('.gallery');
        imagePaths.forEach(image => {
            const imgWrapper = document.createElement('div');
            imgWrapper.classList.add('img-wrapper');
            const imageName = image.split('/').pop();
            imgWrapper.innerHTML = `<img src="${image}" alt="Image" style="width:100px; height:auto;">
                                    <span>${imageName}</span>
                                    <button class="delete-btn" data-image="${image}">Delete</button>`;
            gallery.appendChild(imgWrapper);
        });
    }

    $('.gallery').on('click', '.delete-btn', function() {
        const imagePath = $(this).data('image');
        deleteImage(imagePath);
    });

    async function deleteImage(imagePath) {
        const response = await fetch(`${baseUrl}/php/deleteImage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({imagePath})
        });
        const result = await response.json();
        if (result.success) {
            alert('Image deleted successfully!');
            // Clear gallery 
            $('.gallery').empty();
            // Current orientation
            const orientation = document.querySelector('.radio-group input[type="radio"]:checked').value;
            fetchImages(orientation);
        } else {
            alert('Image deletion failed!');
        }
    }

    // Get all radio inputs within the radio-group class
    var radios = document.querySelectorAll('.radio-group input[type="radio"]');

    // Add an event listener to each radio button
    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Log the change of value when a radio button is selected
            if (this.checked) {
                const orientation = this.value;
                fetchImages(orientation);
            }
        });
    });

    checkUploadSuccessParameter();
    fetchImages('horizontal');
});