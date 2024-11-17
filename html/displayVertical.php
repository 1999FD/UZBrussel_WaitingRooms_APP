<?php
// Extract the ID from the URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loketten UZBrussel</title>
    <script src="../js/config.js"></script>
    <script src="../js/vue.js"></script>
    <script src="../js/xml2json.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <audio id="notificationSound" src="../media/Noti.mp3"></audio>
    <!-- Green vertical bar -->
    <div class="top-container">
        <div class="green-bar"></div>
        <!-- Content of the page -->
        <div id="app" class="content-container">
            <div class="overlay" v-if="isNewTicketVisible"></div>
            <div class="entry-container">
                <div class="headers" class="row">
                    <div class="label">TICKET</div>
                    <div class="label">LOKET</div>
                </div>

                <div class="row values" v-for="(entry, index) in historyEntries" :key="index">
                    <div class="value value1">{{ entry.ticket }}</div>
                    <div class="arrow-container">
                        <i style="font-size: 6rem;" class="fas fa-arrow-right"></i>
                    </div>
                    <div class="value value2">{{ entry.stationName }}</div>
                </div>
            </div>
            
            <div class="new-ticket-popup" v-if="isNewTicketVisible" ref="popup">
                <div class="popup-ticket-display">
                    <div class="popup-ticket-section">
                        <div class="popup-label">TICKET</div>
                        <div class="popup-value">{{ lastTicket.ticket }}</div>
                    </div>
                    <i class="popup-arrow fas fa-arrow-right"></i>
                    <div class="popup-station-section">
                        <div class="popup-label" style="text-align: left;">LOKET</div>
                        <div class="popup-value">{{ lastTicket.stationName }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bottom-container">
        <!-- Place Image -->
        <!-- <img src="../img/4_3_test.jpg" class="background" alt="UZBrussel"> -->

        <iframe id="iFrameObj" style="border: 0; background-color: black" class="background" src="https://lg-player.cloudsignage.be/?mode=view-playlist&id=386"></iframe>
        <div style="display: none;" class="background-text">
            <h1 id="backgroundText"></h1>
        </div>
    </div>

    <script src="../js/display.js"></script>
    <script>
        const id = <?php echo $id; ?>;
        new Vue({
            el: '#app',
            data() {
                return {
                    lastTicket: null,
                    historyEntries: [],
                    isNewTicketVisible: false,
                    isFirstLoad: true, // Flag to track first load
                    currentLoketIds: null
                };
            },
            methods: {
                showNewTicketPopup() {
                    this.isNewTicketVisible = true;
                    this.$nextTick(() => {
                        const popup = this.$refs.popup;
                        const overlay = document.querySelector('.overlay');
                        popup.classList.add('active');
                        overlay.classList.add('active');
                        
                        setTimeout(() => {
                            popup.classList.add('fade-out');
                            
                            setTimeout(() => {
                                this.isNewTicketVisible = false;
                                popup.classList.remove('active', 'fade-out');
                                overlay.classList.remove('active');
                            }, 1000); // This matches the fade-out duration
                        }, 500000); // Popup stays visible for 4500ms before fading out
                    });
                },
                async fetchBackgroundTextAndOrientation(){
                    try{
                        const response = await fetch(`${baseUrl}/data.json?${Date.now()}`); // Fetch data.json with timestamp
                        var jsonData = await response.json();
                        jsonData = jsonData.find(item => item.displayId === id.toString());
                        const orientation = jsonData.orientationName;
                        const contentName = jsonData.content
                        if(contentName !== this.currentContentName){
                            const responseContent = await fetch("https://signage.iddprojects.be/api/playlists-idd-iptv/24");
                            const departments = await responseContent.json();
                            const departmentsArray = Object.values(departments.departments);
                            playlists = departmentsArray.find((item) => item.department_name === "UZB_Loketten").playlists;
                            const playlist = playlists.find((item) => item.name === contentName);
                            this.currentContentName = playlist.name;
                            const iFrameObj = document.getElementById('iFrameObj');
                            iFrameObj.src = playlist.url;
                            // iFrameObj.src = "https://lg-player.iddprojects.be?mode=view-playlist&id=1960"
                        }
                        // if(orientation === 'vertical'){
                        //     window.location.href = `${baseUrl}/html/displayVertical.php?id=${id}`;
                        // }
                        loadCSS(orientation)
                    } catch(error) {
                        console.error('Error fetching data.json:', error);
                    }
                },
                async fetchXMLData() {
                    try {
                        const response = await fetch(`${baseUrl}/data.json?${Date.now()}`); // Fetch data.json with timestamp
                        var jsonData = await response.json();
                        const displayInfo = jsonData.find(item => item.displayId === id.toString());
                        // If current loketIDs are different from the new ones then update the currentLoketIds
                        // Remove last element of currentLoketIds and keep rest
                        displayInfo.loketIDs.forEach(i => {
                            if(!this.currentLoketIds.includes(i)){
                                location.reload();
                            }
                        })
                        this.currentLoketIds.forEach(i => {
                            if(!displayInfo.loketIDs.includes(i)){
                                location.reload();
                            }
                        })
                        displayInfo.loketIDs.forEach(async loket => {
                            try{
                                const response = await fetch(`${baseUrl}/Shares/AFROEPNUMMERS/${loket}.xml?${Date.now()}`);
                                if(response.ok){
                                    const xmlText = await response.text();
                                    const parser = new DOMParser();
                                    const xmlDoc = parser.parseFromString(xmlText, 'text/xml');
                                    const newTicket = this.xmlToJson(xmlDoc);
                                    const isSameTicket = this.historyEntries.find(item => item.ticket === newTicket.ticket && item.station === newTicket.station);
                                    // Check if newTicket was changed
                                    if(!isSameTicket){
                                        const exists = this.historyEntries.findIndex(item => item.station === newTicket.station)
                                        if (exists < 0) {    
                                            // If no ticket exists with such station then just add it                            
                                            this.historyEntries.unshift(newTicket)
                                        }else{
                                            // If a ticket exists with such a station then remove the old ticket with such station and add the new one
                                            this.historyEntries.splice(exists, 1)
                                            this.historyEntries.unshift(newTicket)
                                        }
                                        // If history entries contains more than 5 tickets remove last one
                                        if(this.historyEntries.length > 7){
                                            this.historyEntries.pop()
                                        }
                                        this.lastTicket = newTicket;
                                        if (!this.isFirstLoad) {
                                            this.showNewTicketPopup();
                                            playNotificationSound();
                                        }
                                    }
                                }
                            }catch (error) {
                                // console.error('Error fetching XML:', error);
                            }
                        });
                        this.fetchBackgroundTextAndOrientation();

                    } catch (error) {
                        // console.error('Error fetching XML:', error);
                        this.fetchBackgroundTextAndOrientation();
                    }
                },
                xmlToJson(xml) {
                    const jsonObj = JSON.parse(xml2json(xml, "")).Ticket;
                    var stationName = jsonObj.loketName.slice(-1);
                    // Check if stationName is a number 
                    if(isNaN(stationName)){
                        stationName = jsonObj.loketName;
                    }
                    return {
                        "ticket": jsonObj.ticketNr,
                        "station": jsonObj.loketId,
                        "stationName": stationName
                    }
                },
                async updateDataPeriodically() {
                    // Fetch XML data every second
                    setInterval(() => {
                        this.fetchXMLData();
                    }, 1000);
                }
            },
            async created() {
                // Initialize without showing popup or sound
                // Set timeout of 3 seconds
                const response = await fetch(`${baseUrl}/data.json?${Date.now()}`); // Fetch data.json with timestamp
                const jsonData = await response.json();
                const displayInfo = jsonData.find(item => item.displayId === id.toString());
                console.log(jsonData)
                // If there are no loketIDs then return
                if(displayInfo.loketIDs){
                    this.currentLoketIds = displayInfo.loketIDs;
                }
                this.fetchXMLData();
                this.updateDataPeriodically();
                setTimeout(() => {
                    this.isFirstLoad = false;
                }, 3000);
            }
        });
    </script>
</body>
</html>
