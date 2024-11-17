function loadCSS(orientation) {
    // Check if css file not already exists in head
    if (!document.querySelector(`link[href="../css/${orientation}_waitingroom.css"]`)) {
        // Create a new link element
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `../css/${orientation}_waitingroom.css?${Date.now()}`;
        // Append the link element to the head
        document.head.appendChild(link);
    }            
    displayOrientation = orientation;
}