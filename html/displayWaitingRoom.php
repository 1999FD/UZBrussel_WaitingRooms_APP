<?php
// Extract the ID from the URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtzalen UZBrussel</title>
    <script src="../js/config.js"></script>
    <script src="../js/vue.js"></script>
    <script src="../js/xml2json.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <audio id="notificationSound" src="../media/Noti.mp3"></audio>
    <!-- Green vertical bar -->
    <div class="green-bar"></div>
    <!-- Content of the page -->
    <div id="app" class="content-container">
        <div class="header" class="row">
            <div class="label">{{ currentLabel }}</div>
            <!-- Page number -->
            <div class="page-number" v-if="sectionArr.length > 0">{{currentPage}}/{{sectionArr.length}}</div>
        </div>
        <div class="subheader" class="row"></div>
        <div class="content">
            <div class="content-left">
                <div class="section" v-for="(service, index) in services" :key="index" :style="{ display: index >= previousSectionIdx  && index < sectionIdx ? 'block' : 'none' }">
                    <h2>{{ service['Name' + currentLanguage] }}</h2>
                    <div v-for="(unit, index) in service.Units" :key="index">
                        <!-- Check if `unit` is an array -->
                        <template v-if="Array.isArray(unit)">
                            <!-- If `unit` is an array, loop through each item in `unit` -->
                            <div v-for="(item, idx) in unit" :key="idx" class="wait-time">
                                <!-- Conditional clock icon display based on `WaitTimeInMinutes` -->
                                <img v-if="item.WaitTimeInMinutes <= 20" src="../img/clock-green.png" alt="Green Icon" class="icon" />
                                <img v-else-if="item.WaitTimeInMinutes > 20 && item.WaitTimeInMinutes <= 40" src="../img/clock-orange.png" alt="Orange Icon" class="icon" />
                                <img v-else src="../img/clock-red.png" alt="Red Icon" class="icon" />
                                <span class="unit">
                                    <span class="id">{{service.Id}}-{{item.Id}}</span>
                                    <span class="name">{{ item['Name' + currentLanguage] }}</span>
                                </span>
                            </div>
                        </template>

                        <!-- If `unit` is a single object -->
                        <template v-else>
                            <div class="wait-time">
                                <!-- Conditional clock icon display based on `WaitTimeInMinutes` -->
                                <img v-if="unit.WaitTimeInMinutes <= 20" src="../img/clock-green.png" alt="Green Icon" class="icon" />
                                <img v-else-if="unit.WaitTimeInMinutes > 20 && unit.WaitTimeInMinutes <= 40" src="../img/clock-orange.png" alt="Orange Icon" class="icon" />
                                <img v-else src="../img/clock-red.png" alt="Red Icon" class="icon" />
                                <span class="unit">
                                    <span class="id">{{service.Id}}-{{unit.Id}}</span>
                                    <span class="name">{{ unit['Name' + currentLanguage] }}</span>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="content-right">
                <div class="date-time">
                    <div class="time">{{time}}</div>
                    <div class="date">{{date}}</div>
                </div>
                <div class="legend">
                    <h3>{{currentEstimatedWaitingTimeLabel}}:</h3>
                    <div>
                        <img src="../img/clock-green.png" alt="Green Icon" class="icon" /> 0 - 20 min
                    </div>
                    <div>
                        <img src="../img/clock-orange.png" alt="Orange Icon" class="icon" /> 20 - 40 min
                    </div>
                    <div>
                        <img src="../img/clock-red.png" alt="Red Icon" class="icon" /> > 40 min
                    </div>
                </div>
            </div>
        </div>
        <div class="banner" class="row">
            <div class="info-container">
                <i class="fas fa-info-circle" style="margin-right: 8px; color: #555;"></i>
                <div v-html="currentBanner"></div>
            </div>
        </div>
    </div>
    <script src="../js/displayWaitingRoom.js"></script>
    <script>
        const id = <?php echo $id; ?>;
        new Vue({
            el: '#app',
            data() {
                return {
                    waitingRoomId: null,
                    displayId: null,
                    waitingRoomNameNL: null,
                    waitingRoomNameFR: null,
                    waitingRoomNameEN: null,
                    services: [],
                    selectedServices: [],
                    initialSelectedServices: [],
                    currentLanguage: 'NL',
                    currentLabel: 'Wachtzalen UZBrussel',
                    currentEstimatedWaitingTimeLabel: 'Geschatte wachttijd',
                    sectionArr: [],
                    currentPage: 1,
                    sectionIdx: 0,
                    previousSectionIdx: 0,
                    time: '',
                    date: '',
                    currentBanner: ''
                };
            },
            methods: {
                trimText(text) {
                    return text.split('/')[0]; // Returns the part before the "/"
                },
                updateTime() {
                    const now = new Date();
                    // Format time and date as needed
                    this.time = now.toLocaleTimeString('nl-BE', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    this.date = now.toLocaleDateString('nl-BE', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                },
                setHeader() {
                    // Set the header based on the current language
                    if (this.currentLanguage === 'NL') {
                        this.currentLabel = 'Wachttijden';
                        this.currentEstimatedWaitingTimeLabel = 'Geschatte wachttijd';
                    } else if (this.currentLanguage === 'FR') {
                        this.currentLabel = 'Temps d\'attente';
                        this.currentEstimatedWaitingTimeLabel = 'Temps d\'attente estimé';
                    } else {
                        this.currentLabel = 'Waiting Times';
                        this.currentEstimatedWaitingTimeLabel = 'Estimated Waiting Time';
                    }
                },
                setBanner() {
                    // Set the banner based on the current language
                    if (this.currentLanguage === 'FR') {
                        this.currentBanner = 'Le temps d\'attente affiché est une indication. Selon les circonstances, il est possible que le temps d\'attente augmente. </br>Nous vous remercions de votre compréhension.';
                    } else if (this.currentLanguage === 'NL') {
                        this.currentBanner = 'De getoonde wachttijd is een indicatie. Afhankelijk van de omstandigheden kan de wachttijd toenemen. </br>Wij danken u voor uw begrip.';
                    } else {
                        this.currentBanner = 'The displayed waiting time is an indication. Depending on the circumstances, the waiting time may increase. </br>We thank you for your understanding.';
                    }
                },
                changeLanguage() {
                    if (this.currentLanguage === 'NL') {
                        this.currentLanguage = 'FR';
                    } else if (this.currentLanguage === 'FR') {
                        this.currentLanguage = 'EN';
                    } else {
                        this.currentLanguage = 'NL';
                        // Check if last page reached
                        if (this.currentPage === this.sectionArr.length) {
                            this.currentPage = 1;
                            this.sectionIdx = this.sectionArr[0];
                            this.previousSectionIdx = 0;
                        } else {
                            this.currentPage++;
                            this.previousSectionIdx = this.sectionIdx
                            this.sectionIdx += this.sectionArr[this.currentPage - 1];
                        }
                    }
                },
                async fetchOrientation() {
                    try {
                        const response = await fetch(`${baseUrl}/waiting_rooms_data.json?${Date.now()}`); // Fetch data.json with timestamp
                        const jsonData = await response.json();
                        const orientation = jsonData[this.waitingRoomId][this.displayId].orientationName;
                        if (orientation === 'horizontal') {
                            loadCSS(orientation)
                        } else {
                            window.location.href = `${baseUrl}/html/displayWaitingRoomVertical?waitingRoomId=${this.waitingRoomId}&displayId=${this.displayId}`;
                        }
                    } catch (error) {
                        console.error('Error fetching data.json:', error);
                    }
                },
                async fetchWaitingRoomNamesAndSelectedServices() {
                    try {
                        const response = await fetch(`${baseUrl}/Shares/Waitingroom_${this.waitingRoomId}.xml?${Date.now()}`);
                        const xmlText = await response.text(); // Ensure xmlText is retrieved as a string
                        const parser = new DOMParser();
                        const xmlDoc = parser.parseFromString(xmlText, 'text/xml');
                        const waitingRoom = this.xmlToJson(xmlDoc);
                        this.waitingRoomNameNL = waitingRoom.NameNL;
                        this.waitingRoomNameFR = waitingRoom.NameFR;
                        this.waitingRoomNameEN = waitingRoom.NameEN;
                        this.services = waitingRoom.Services.Service
                        // If not a list make a list of it with 1 object
                        if (!Array.isArray(this.services)) {
                            this.services = [this.services];
                        }
                        // If there is a service with more than 10 units, split it into 2 services with same name but half the units
                        this.services.forEach((service, idx) => {
                            // UnitsLength
                            const unitsLength = Array.isArray(service.Units.Unit) ? service.Units.Unit.length : 1;
                            console.log(unitsLength);
                            if (Array.isArray(service.Units.Unit) && unitsLength > 8 && unitsLength <= 17) {
                                const toSlice = service.Units.Unit.length / 2;
                                const newService = JSON.parse(JSON.stringify(service));
                                newService.Units.Unit = service.Units.Unit.slice(toSlice);
                                service.Units.Unit = service.Units.Unit.slice(0, toSlice);
                                this.services.splice(idx + 1, 0, newService);
                            }
                            // If unitsLength larger than 16, divide into 3 services
                            else if (Array.isArray(service.Units.Unit) && unitsLength > 17) {
                                // Calculate slicing points for three parts
                                const toSlice = Math.ceil(unitsLength / 3);

                                // Create two new copies of the service object
                                const newService1 = JSON.parse(JSON.stringify(service));
                                const newService2 = JSON.parse(JSON.stringify(service));

                                // Split the original service into three parts
                                newService1.Units.Unit = service.Units.Unit.slice(toSlice, toSlice * 2);
                                newService2.Units.Unit = service.Units.Unit.slice(toSlice * 2);
                                service.Units.Unit = service.Units.Unit.slice(0, toSlice);

                                // Insert the new services after the original
                                this.services.splice(idx + 1, 0, newService1);
                                this.services.splice(idx + 2, 0, newService2);
                            }
                        });
                        // Sort all the services by the amount of units they have
                        this.services.sort((a, b) => {
                            // Get the length of units for both 'a' and 'b'. If 'Units.Unit' is not an array, assume length is 1.
                            const aLength = Array.isArray(a.Units.Unit) ? a.Units.Unit.length : 1;
                            const bLength = Array.isArray(b.Units.Unit) ? b.Units.Unit.length : 1;

                            // Return comparison value for sorting
                            return aLength - bLength;
                        });
                        let countedUnits = 0;
                        let sectionsToShow = 0;
                        let totalPages = 0; // Reset total pages before counting
                        let unitsPerPage = 9; // Set the number of units per page
                        this.services.forEach((service, idx) => {
                            let unitsInService = Array.isArray(service.Units.Unit) ? service.Units.Unit.length : 1;
                            unitsInService += 1; // Because we have to count the service header as well
                            // Track units for each page
                            if (countedUnits + unitsInService <= unitsPerPage) {
                                sectionsToShow++;
                                countedUnits += unitsInService;
                            } else {
                                // Increment page count if limit is exceeded and reset counter
                                totalPages++;
                                this.sectionArr.push(sectionsToShow);
                                countedUnits = unitsInService;
                                sectionsToShow = 1;
                            }
                        });
                        // Add an additional page for remaining units if needed
                        if (countedUnits > 0 || sectionsToShow > 0) {
                            totalPages++;
                            this.sectionArr.push(sectionsToShow);
                        }
                        this.sectionIdx = this.sectionArr[0];
                    } catch (error) {
                        console.error('Error fetching data.json:', error);
                    }
                },
                async fetchXMLData() {
                    try {
                        this.fetchOrientation();
                        const response = await fetch(`${baseUrl}/Shares/Waitingroom_${this.waitingRoomId}.xml?${Date.now()}`);
                        const xmlText = await response.text(); // Ensure xmlText is retrieved as a string
                        const parser = new DOMParser();
                        const xmlDoc = parser.parseFromString(xmlText, 'text/xml');
                        var newServices = this.xmlToJson(xmlDoc).Services.Service;
                        const response2 = await fetch(`${baseUrl}/waiting_rooms_data.json?${Date.now()}`); // Fetch data.json with timestamp
                        const jsonData = await response2.json();
                        // If newServices is not an array, make it an array
                        if (!Array.isArray(newServices)) {
                            newServices = [newServices];
                        }
                        // Check if selected services have changed from initial selected services and reload page if they have
                        const serviceIds = this.services.map(service => service.Id);
                        const newServiceIds = newServices.map(service => service.Id);
                        newServiceIds.forEach(i => {
                            if (!serviceIds.includes(i)) {
                                location.reload();
                            }
                        })
                        serviceIds.forEach(i => {
                            if (!newServiceIds.includes(i)) {
                                location.reload();
                            }
                        })

                        // Update WaitTimeInMinutes in selectedServices based on newSelectedServices
                        this.services.forEach((existingService, serviceIndex) => {
                            const newService = newServices.find(service => service.Id === existingService.Id);
                            if (newService) {
                                if (Array.isArray(existingService.Units.Unit)) {
                                    // If Units is an array, loop through each unit
                                    existingService.Units.Unit.forEach((existingUnit, unitIndex) => {
                                        const newUnit = newService.Units.Unit.find(unit => unit.Id === existingUnit.Id);
                                        if (newUnit) {
                                            // Only update if the WaitTimeInMinutes differs
                                            if (existingUnit.WaitTimeInMinutes !== newUnit.WaitTimeInMinutes) {
                                                console.log(`Updating WaitTimeInMinutes for unit ${existingUnit.Id}: ${existingUnit.WaitTimeInMinutes} -> ${newUnit.WaitTimeInMinutes}`);
                                                Vue.set(existingUnit, 'WaitTimeInMinutes', newUnit.WaitTimeInMinutes);
                                            }
                                        }
                                    });
                                } else {
                                    // If Units is a single object
                                    const newUnit = newService.Units.Unit;
                                    if (newUnit && existingService.Units.Unit.Id === newUnit.Id) {
                                        // Only update if the WaitTimeInMinutes differs
                                        if (existingService.Units.Unit.WaitTimeInMinutes !== newUnit.WaitTimeInMinutes) {
                                            console.log(`Updating WaitTimeInMinutes for unit ${existingService.Units.Unit.Id}: ${existingService.Units.Unit.WaitTimeInMinutes} -> ${newUnit.WaitTimeInMinutes}`);
                                            Vue.set(existingService.Units.Unit, 'WaitTimeInMinutes', newUnit.WaitTimeInMinutes);
                                        }
                                    }
                                }
                            }
                        });


                    } catch (error) {
                        this.fetchOrientation();
                    }
                },
                xmlToJson(xml) {
                    const jsonObj = JSON.parse(xml2json(xml, "")).data.Waitingroom;
                    return jsonObj;
                },
                async updateDataPeriodically() {
                    // Fetch XML data every second
                    setInterval(() => {
                        this.fetchXMLData();
                        this.changeLanguage();
                        this.setHeader();
                        this.setBanner();
                    }, 10000); // 10 seconds
                    setInterval(() => {
                        this.updateTime();
                    }, 30000);
                }
            },
            created() {
                const urlParams = new URLSearchParams(window.location.search);
                this.waitingRoomId = urlParams.get('waitingRoomId');
                this.displayId = urlParams.get('displayId');
                this.fetchWaitingRoomNamesAndSelectedServices();
                this.fetchXMLData();
                this.setHeader();
                this.setBanner();
                this.updateTime();
            },
            mounted() {
                this.updateDataPeriodically();
            }
        });
    </script>
</body>

</html>