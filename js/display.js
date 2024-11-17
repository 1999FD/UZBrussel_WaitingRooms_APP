let slides = [];
let slideIndex = 0;

// Load the CSS file based on the device orientation
const backgroundImage = document.querySelector('.background');
backgroundImage.addEventListener('click', () => {
    console.log("AUDIO ACTIVATED")
    const audio = document.getElementById('notificationSound');
    audio.play();
}, { once: true });

// Description: Fetch images from the server
async function fetchImages() {
    try {
        const response = await fetch(`${baseUrl}/php/listImages`);
        slides = await response.json(); // Parse JSON response
        const response2 = await fetch(`${baseUrl}/data.json?${Date.now()}`); // Fetch data.json with timestamp
        const jsonData = await response2.json(); // Parse JSON response
        const orientation = jsonData[id]["orientationName"];
        slides = slides[orientation];
        // Update the background image URL
        const background = document.querySelector('.background');
        const img = new Image();
        img.src = slides[slideIndex];
        // Once the image is fully loaded
        img.onload = function() {
            // Set the background image
            background.style.backgroundImage = `url('${img.src}')`;
        };
        slideIndex += 1;
        slideIndex = slideIndex % slides.length
    } catch (error) {
        console.error('Failed to fetch images:', error);
    }
}

// Description: Load the CSS file based on the device orientation
function loadCSS(orientation) {
    // Check if css file not already exists in head
    if (!document.querySelector(`link[href="../css/${orientation}.css"]`)) {
        // Create a new link element
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `../css/${orientation}.css?${Date.now()}`;
        // Append the link element to the head
        document.head.appendChild(link);
    }            
    if(!document.querySelector(`link[href="../css/${orientation}_popup.css"]`)){
        const link2 = document.createElement('link');
        link2.rel = 'stylesheet';
        link2.href = `../css/${orientation}_popup.css?${Date.now()}`;
        // Append the link element to the head
        document.head.appendChild(link2);
    }
    displayOrientation = orientation;
}

// Description: Play notification sound
function playNotificationSound() {
    console.log("PLAYED SOUND")
    const audio = document.getElementById('notificationSound');
    audio.play();
}

async function updateSlidePeriodically() {
    setInterval(() => {
        const background = document.querySelector('.background');
        const img = new Image();
        img.src = slides[slideIndex];
        // Once the image is fully loaded
        img.onload = function() {
            // Set the background image
            background.style.backgroundImage = `url('${img.src}')`;
        };
        slideIndex += 1;
        // Reset slideIndex if it exceeds the number of images
        slideIndex = slideIndex % slides.length
    }, 30000);
}

// Update every 30 minutes one time
async function updateSetOfImagesPeriodically(){
    setInterval(() => {
        // fetchImages();
    }, 1800000);
}

// fetchImages();
updateSlidePeriodically();
updateSetOfImagesPeriodically();
