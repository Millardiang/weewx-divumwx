document.addEventListener('DOMContentLoaded', function() {
    function updateStatusHeader(allVerified) {
        const statusHeader = document.getElementById('status-header');
        if (allVerified) {
            statusHeader.textContent = 'All systems operational';
            statusHeader.classList.remove('warning', 'error');
            statusHeader.classList.add('success');
        } else {
            statusHeader.textContent = 'Issues detected';
            statusHeader.classList.remove('warning', 'success');
            statusHeader.classList.add('error');
        }
    }

    function handleError(item, statusElement, detailsElement, message) {
        statusElement.textContent = 'Issue';
        statusElement.classList.add('error');
        detailsElement.innerHTML = `<p>${message}</p>`;
    }

    function performCheck() {
        fetch('chkReq.php')
            .then(response => response.json())
            .then(data => {
                let allVerified = true;

                function updateStatus(statusId, detailId, statusText, classText, detailsHtml) {
                    document.getElementById(statusId).textContent = statusText;
                    document.getElementById(statusId).classList.add(classText);
                    if (detailId && detailsHtml) {
                        document.getElementById(detailId).innerHTML = detailsHtml;
                    }
                }

                function checkPhpVersion() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            let phpVersionStatus = data.php_version.status;
                            let phpVersionDetails = `<p>Running Version: ${data.php_version.version}</p>`;
                            if (phpVersionStatus) {
                                updateStatus('php-version-status', 'php-version-details', 'Verified', 'success', phpVersionDetails);
                                document.getElementById('php-version-details').classList.add('hidden');
                            } else {
                                handleError('php-version', document.getElementById('php-version-status'), document.getElementById('php-version-details'), 'PHP version is too low');
                                document.getElementById('php-version-details').classList.remove('hidden');
                            }
                            resolve();
                        }, 1500);
                    });
                }

                function checkWeewx() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            let weewxStatus = data.weewx.status;
                            let weewxDetails = `<p>Running Version: ${data.weewx.version}</p>`;
                            if (weewxStatus) {
                                updateStatus('weewx-status', 'weewx-details', 'Verified', 'success', weewxDetails);
                                document.getElementById('weewx-details').classList.add('hidden');
                            } else {
                                handleError('weewx', document.getElementById('weewx-status'), document.getElementById('weewx-details'), 'WeeWX not found');
                                document.getElementById('weewx-details').classList.remove('hidden');
                            }
                            resolve();
                        }, 1500);
                    });
                }

                function checkPhpModules() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            let phpModulesVerified = true;
                            let phpModulesDetails = '<table><tr><th>Module</th><th>Status</th></tr>';
                            let issuesFound = false;
                            for (const module in data.php_modules) {
                                let moduleStatus = data.php_modules[module] === 'Loaded' 
                                    ? `<span style="color: green;">${data.php_modules[module]}</span>`
                                    : `<span style="color: red;">${data.php_modules[module]}</span>`;
                                phpModulesDetails += `<tr><td>${module}</td><td>${moduleStatus}</td></tr>`;
                                if (data.php_modules[module] !== 'Loaded') {
                                    phpModulesVerified = false;
                                    issuesFound = true; // ? Mark issues found
                                }
                            }
                            phpModulesDetails += '</table>';
                            document.getElementById('php-modules-details').innerHTML = phpModulesDetails;
                            if (issuesFound) {
                                document.getElementById('php-modules-details').classList.remove('hidden'); // ? Show on issues
                            } else {
                                document.getElementById('php-modules-details').classList.add('hidden'); // ? Hide on success
                            }
                            if (phpModulesVerified) {
                                updateStatus('php-modules-status', null, 'Verified', 'success');
                            } else {
                                allVerified = false;
                            }
                            resolve();
                        }, 1500);
                    });
                }

                function checkDirectories() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            let directoriesVerified = true;
                            let directoryDetails = '<table><tr><th>Directory</th><th>Status</th><th>Details</th></tr>';
                            let issuesFound = false;
                            for (const dir in data.directories) {
                                const status = data.directories[dir].status ? 'Verified' : 'Issue';
                                const statusClass = data.directories[dir].status ? 'success' : 'error';
                                const details = data.directories[dir].status ? 'None' : (data.directories[dir].message || 'Issue detected');
                                directoryDetails += `<tr>
                                    <td>${dir}</td>
                                    <td><span class="${statusClass}">${status}</span></td>
                                    <td>${details}</td>
                                </tr>`;
                                if (!data.directories[dir].status) {
                                    directoriesVerified = false;
                                    issuesFound = true;
                                }
                            }
                            directoryDetails += '</table>';
                            document.getElementById('directory-details').innerHTML = directoryDetails;
                            if (issuesFound) {
                                document.getElementById('directory-details').classList.remove('hidden');
                            } else {
                                document.getElementById('directory-details').classList.add('hidden');
                            }
                            if (directoriesVerified) {
                                updateStatus('directory-status', null, 'Verified', 'success');
                            } else {
                                handleError('directories', document.getElementById('directory-status'), document.getElementById('directory-details'), 'Some directories have issues');
                            }
                            resolve();
                        }, 1500);
                    });
                }

                function checkDatabase() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            const dbStatusElement = document.getElementById('db-check-status');
                            const dbDetailsElement = document.getElementById('db-check-details');
                            let dbFileIssue = "";
                            let dbConnectionIssue = "";
                            if (data.db_file && !data.db_file.status) {
                                dbFileIssue = `<p><strong style="color: black;">Database file issue:</strong> <span style="color: red;">${data.db_file.message}</span></p>`;
                            }
                            if (data.db_connection && !data.db_connection.status) {
                                dbConnectionIssue = `<p><strong style="color: black;">Database connection issue:</strong> <span style="color: red;">${data.db_connection.message}</span></p>`;
                            }
                            if (!dbFileIssue && !dbConnectionIssue) {
                                updateStatus('db-check-status', 'db-check-details', 'Verified', 'success', `<p>Database is accessible and permissions are correct.</p>`);
                            } else {
                                let issueMessage = dbFileIssue + dbConnectionIssue;
                                updateStatus('db-check-status', null, 'Issue', 'error');
                                dbDetailsElement.innerHTML = issueMessage;
                                dbDetailsElement.classList.remove('hidden');
                                allVerified = false;
                            }
                            resolve();
                        }, 1500);
                    });
                }

            async function runChecks() {
                await checkPhpVersion();
                await checkWeewx();
                await checkPhpModules();
                await checkDirectories();
                await checkDatabase();

                updateStatusHeader(allVerified);

                if (allVerified) {
                    document.getElementById('next-button').disabled = false;
                    document.getElementById('retry-button').classList.add('hidden');
                } else {
                    document.getElementById('next-button').disabled = true;
                    document.getElementById('retry-button').classList.remove('hidden');
                }
            }

            runChecks();

        })
        .catch(error => {
            console.error('Error fetching status:', error);
            document.getElementById('status-header').textContent = 'Error fetching status';
            document.getElementById('status-header').classList.remove('warning', 'success');
            document.getElementById('status-header').classList.add('error');
            document.getElementById('retry-button').classList.remove('hidden');
        });
    }

    document.querySelectorAll('.status-label').forEach(function(label) {
        label.addEventListener('click', function() {
            const item = label.closest('.status-item');
            const details = item.nextElementSibling;
            details.classList.toggle('hidden');
        });
    });

    performCheck();

    document.getElementById('retry-button').addEventListener('click', function() {
        if (typeof csrfToken === 'undefined' || !csrfToken) {
            return;
        }
        document.getElementById('next-button').disabled = true;
        fetch('reset_session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrfToken })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            }
        })
        .catch(error => console.error("Fetch error:", error));
    });
    

    function saveConfigToServer(configArray) {
        const statusElement = document.getElementById('file-writing-status');
        const fileInfoStatus = document.getElementById('file-info-status');

        fetch('createUSF.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(configArray)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                statusElement.textContent = "userSettings.php file written successfully.";
                statusElement.style.color = "green";
                fileInfoStatus.textContent = `Owner: ${data.owner}, Group: ${data.group}, Permissions: ${data.permissions}\n\nYou may want to manually change the owner:group and the permissions of the\nfile to match the other files in your DivumWX directory as we (webserver)\ndo not have the permissions to do so.`;
                fileInfoStatus.classList.remove('hidden');
                document.getElementById('next-button').disabled = false; // Enable the "Next" button in TAB 2
            } else {
                statusElement.textContent = data.message || "Unknown error.";
                statusElement.style.color = "red";
                fileInfoStatus.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusElement.textContent = "Error writing userSettings.php file.";
            statusElement.style.color = "red";
        });
    }

    document.getElementById('next-button').addEventListener('click', function() {
        const activeTab = document.querySelector('.tab.active').id;
        if (activeTab === 'tab1') {
            switchToTab('tab2');
            document.getElementById('next-button').disabled = true;
            const statusElement = document.getElementById('file-writing-status');
            statusElement.textContent = "Writing userSettings.php ..........";
            statusElement.style.color = "black";
            setTimeout(function() {
                saveConfigToServer(configArray);
            }, 2500);
        } else if (activeTab === 'tab2') {
            switchToTab('tab3');
            document.getElementById('next-button').disabled = true;
        } else if (activeTab === 'tab3') {
            window.location.href = 'admin/index.php';
        }
    });

    function switchToTab(tabId) {
        document.querySelectorAll('.tab').forEach(function(tab) {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');
        document.querySelectorAll('.tab-button').forEach(function(button) {
            button.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
    }

    document.getElementById('password-submit-button').addEventListener('click', function() {
        const username = document.getElementById('admin-username').value.trim();
        const password = document.getElementById('password').value.trim();
        const passwordMessage = document.getElementById('password-message');
        if (username.length < 3) {
            passwordMessage.textContent = "Username must be at least 3 characters long.";
            passwordMessage.style.color = "red";
            passwordMessage.classList.remove('hidden');
            return;
        }
        if (password.length < 8) {
            passwordMessage.textContent = "Password must be at least 8 characters long.";
            passwordMessage.style.color = "red";
            passwordMessage.classList.remove('hidden');
            return;
        }

        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);

        fetch('dvmActSetupPwd.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                passwordMessage.textContent = data.message;
                passwordMessage.style.color = "green";
                passwordMessage.classList.remove('hidden');
                document.getElementById('next-button').disabled = false;
            } else {
                passwordMessage.textContent = data.message;
                passwordMessage.style.color = "red";
                passwordMessage.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            passwordMessage.textContent = "Error updating admin credentials.";
            passwordMessage.style.color = "red";
            passwordMessage.classList.remove('hidden');
        });
    });

    const configArray = [
        { key: 'TZ', value:'GMT0' },
        { key: 'extralinks', value:'no' },
        { key: 'localIP', value:'10.10.10.10' },
        { key: 'webSrvr', value:'Apache' },
        { key: 'trkVisits', value:'0' },
        { key: 'stripLocal', value:'0' },
        { key: 'osLogo', value:'Arch' },
        { key: 'os', value:'Arch Linux' },
        { key: 'sbLang', value:'no' },
        { key: 'weatherflowID', value:'' },
        { key: 'weatherflowoption', value:'no' },
        { key: 'weatherflowmapzoom', value:'11' },
        { key: 'dateFormat', value:'d-m-Y' },
        { key: 'timeFormat', value:'H:i:s' },
        { key: 'timeFormatShort', value:'H:i' },
        { key: 'clockformat', value:'24' },
        { key: 'advisoryzone', value:'eu' },
        { key: 'advisoryregion', value:'' },
        { key: 'englishFloodLocation', value:'' },
        { key: 'aqInUse', value:'no' },
        { key: 'aqZone', value:'ci' },
        { key: 'aqSource', value:'weewx' },
        { key: 'lightningSource', value:'0' },
        { key: 'stationAbbrev', value:'MySta' },
        { key: 'position1', value:'dvmClockOutlookModule.php' },
        { key: 'position2', value:'dvmCurrentModule.php' },
        { key: 'position3', value:'dvmWebcamModule.php' },
        { key: 'position4', value:'dvmForecastModule.php' },
        { key: 'position5', value:'dvmTemperatureModule.php' },
        { key: 'position6', value:'dvmAnemometerModule.php' },
        { key: 'position7', value:'dvmWindModule.php' },
        { key: 'position8', value:'dvmBarometerModule.php' },
        { key: 'position9', value:'dvmRainfallModule.php' },
        { key: 'position10', value:'dvmPollenModule.php' },
        { key: 'position11', value:'dvmGreenhouseGasModule.php' },
        { key: 'position12', value:'dvmSolarUvLuxModule.php' },
        { key: 'position13', value:'dvmEarthDaylightModule.php' },
        { key: 'position14', value:'dvmSolarDialModule.php' },
        { key: 'position15', value:'dvmGeocentricModule.php' },
        { key: 'position16', value:'dvmMoonPhaseModule.php' },
        { key: 'position17', value:'dvmLightningModule.php' },
        { key: 'position18', value:'dvmEarthquakeModule.php' },
        { key: 'position19', value:'dvmIndoorTemperatureModule.php' },
        { key: 'position20', value:'dvmAirqualityModule.php' },
        { key: 'webcamurl', value:'' },
        { key: 'videoWeatherCamURL', value:'' },
        { key: 'email', value:'' },
        { key: 'twitter', value:'' },
        { key: 'since', value:'' },
        { key: 'defaultlanguage', value:'en' },
        { key: 'flag', value:'gb' },
        { key: 'manifestShortName', value:'' },
        { key: 'notifications', value:'yes' },
        { key: 'notifyWind', value:'yes' },
        { key: 'notifyEarthquake', value:'yes' },
        { key: 'notifyMagnitude', value:'3' },
        { key: 'linkWU', value:'no' },
        { key: 'linkWUNewDash', value:'no' },
        { key: 'WUid', value:'' },
        { key: 'linkCWOPID', value:'no' },
        { key: 'linkFindUID', value:'no' },
        { key: 'linkNOAA', value:'no' },
        { key: 'linkMADIS', value:'no' },
        { key: 'linkMesoWest', value:'no' },
        { key: 'linkWeatherCloudID', value:'' },
        { key: 'linkWindyID', value:'' },
        { key: 'linkAWEKASID', value:'' },
        { key: 'linkAmbientWeatherID', value:'' },
        { key: 'linkPWSWeatherID', value:'' },
        { key: 'linkMetOfficeID', value:'' },
        { key: 'linkCustom1Title', value:'' },
        { key: 'linkCustom1URL', value:'' },
        { key: 'linkCustom2Title', value:'' },
        { key: 'linkCustom2URL', value:'' },
        { key: 'USAWeatherFinder', value:'' },
        { key: 'extraLinkTitle', value:'' },
        { key: 'extraLinkColor', value:'' },
        { key: 'extraLinkURL', value:'' }
    ];
});

