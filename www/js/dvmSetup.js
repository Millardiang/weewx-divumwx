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
                            if (data.php_version.status) {
                                updateStatus('php-version-status', 'php-version-details', 'Verified', 'success', `<p>Version: ${data.php_version.version}</p>`);
                            } else {
                                handleError('php-version', document.getElementById('php-version-details'), 'PHP version is too low');
                                allVerified = false;
                            }
                            resolve();
                        }, 1500);
                    });
                }

                function checkWeewx() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            if (data.weewx.status) {
                                updateStatus('weewx-status', 'weewx-details', 'Verified', 'success', `<p>Version: ${data.weewx.version}</p>`);
                            } else {
                                handleError('weewx', document.getElementById('weewx-status'), document.getElementById('weewx-details'), 'WeeWX not found');
                                allVerified = false;
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
                            for (const module in data.php_modules) {
                                phpModulesDetails += `<tr><td>${module}</td><td>${data.php_modules[module]}</td></tr>`;
                                if (data.php_modules[module] !== 'Loaded') {
                                    phpModulesVerified = false;
                                }
                            }
                            phpModulesDetails += '</table>';
                            document.getElementById('php-modules-details').innerHTML = phpModulesDetails;
                            if (phpModulesVerified) {
                                updateStatus('php-modules-status', null, 'Verified', 'success');
                            } else {
                                handleError('php-modules', document.getElementById('php-modules-status'), document.getElementById('php-modules-details'), 'Some PHP modules are missing');
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
                            let directoryDetails = '<table><tr><th>Directory</th><th>Status</th></tr>';
                            for (const dir in data.directories) {
                                const status = data.directories[dir].status ? 'Verified' : 'Issue';
                                const statusClass = data.directories[dir].status ? 'success' : 'error';
                                directoryDetails += `<tr><td>${dir}</td><td><span class="${statusClass}">${status}</span></td></tr>`;
                                if (!data.directories[dir].status) {
                                    directoriesVerified = false;
                                }
                            }
                            directoryDetails += '</table>';
                            document.getElementById('directory-details').innerHTML = directoryDetails;
                            if (directoriesVerified) {
                                updateStatus('directory-status', null, 'Verified', 'success');
                            } else {
                                handleError('directories', document.getElementById('directory-status'), document.getElementById('directory-details'), 'Some directories have issues');
                                allVerified = false;
                            }
                            resolve();
                        }, 1500);
                    });
                }

                function checkDatabase() {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            if (data.db_file.status) {
                                updateStatus('db-check-status', 'db-check-details', 'Verified', 'success', `<p>Database is accessible and permissions are correct.</p>`);
                            } else {
                                handleError('db-check', document.getElementById('db-check-status'), document.getElementById('db-check-details'), data.db_file.message);
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
        document.getElementById('next-button').disabled = true; // Disable the "Next" button
        performCheck();
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
        const password = document.getElementById('password').value;
        const passwordMessage = document.getElementById('password-message');

        if (password.length < 8) {
            passwordMessage.textContent = "Password must be at least 8 characters long.";
            passwordMessage.style.color = "red";
            passwordMessage.classList.remove('hidden');
            return;
        }

        const formData = new FormData();
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
            passwordMessage.textContent = "Error updating password.";
            passwordMessage.style.color = "red";
            passwordMessage.classList.remove('hidden');
        });
    });

    const configArray = [
        { key: 'themelayout', value: '5' },
        { key: 'TZ', value: 'Europe/London' },
        { key: 'trkVisits', value: 1 },
        { key: 'stripLocal', value: 1 },
        { key: 'localIP', value: '0.0.0.0' },
        { key: 'webSrvr', value: 0 },
        { key: 'extralinks', value: 'yes' },
        { key: 'sbLang', value: 'yes' },
        { key: 'dateFormat', value: 'd-m-Y' },
        { key: 'timeFormat', value: 'H:i:s' },
        { key: 'timeFormatShort', value: 'H:i' },
        { key: 'clockformat', value: '24' },
        { key: 'advisoryzone', value: 'uk' },
        { key: 'aqInUse', value: 'yes' },
        { key: 'aqZone', value: 'uk' },
        { key: 'aqSource', value: 'weewx' },
        { key: 'lightningSource', value: 1 },
        { key: 'position2', value: 'dvmAirqualityTop.php' },
        { key: 'position3', value: 'dvmLightningTop.php' },
        { key: 'position5', value: 'dvmTemperatureModule.php' },
        { key: 'position6', value: 'dvmForecastModule.php' },
        { key: 'position7', value: 'dvmCurrentModule.php' },
        { key: 'position8', value: 'dvmWindModule.php' },
        { key: 'position9', value: 'dvmRainfallModule.php' },
        { key: 'position10', value: 'dvmBarometerModule.php' },
        { key: 'position11', value: 'dvmIndoorTemperatureModule.php' },
        { key: 'position12', value: 'dvmSolarUvLuxModule.php' },
        { key: 'position13', value: 'dvmLightningModule.php' },
        { key: 'position14', value: 'dvmAirqualityModule.php' },
        { key: 'position15', value: 'dvmWebcamModule.php' },
        { key: 'position16', value: 'dvmEarthquakeModule.php' },
        { key: 'position17', value: 'dvmEarthDaylightModule.php' },
        { key: 'position18', value: 'dvmSolarDialModule.php' },
        { key: 'position19', value: 'dvmMoonPhaseModule.php' },
        { key: 'webcamurl', value: '' },
        { key: 'videoWeatherCamURL', value: '' },
        { key: 'email', value: 'example@me.com' },
        { key: 'since', value: '2000' },
        { key: 'defaultlanguage', value: 'en' },
        { key: 'flag', value: 'en' },
        { key: 'manifestShortName', value: 'MYWX' },
        { key: 'notifications', value: 'yes' },
        { key: 'notifyWind', value: 'yes' },
        { key: 'notifyEarthquake', value: 'yes' },
        { key: 'notifyMagnitude', value: 3 },
        { key: 'linkWU', value: 'yes' },
        { key: 'linkWUNewDash', value: 'yes' },
        { key: 'WUid', value: 'YOURWUKEY' },
        { key: 'linkCWOPID', value: 'no' },
        { key: 'linkFindUID', value: 'no' },
        { key: 'linkNOAA', value: 'no' },
        { key: 'linkMADIS', value: 'no' },
        { key: 'linkMesoWest', value: 'no' },
        { key: 'linkWeatherCloudID', value: '' },
        { key: 'linkWindyID', value: '' },
        { key: 'linkAWEKASID', value: '' },
        { key: 'linkAmbientWeatherID', value: '' },
        { key: 'linkPWSWeatherID', value: '' },
        { key: 'linkMetOfficeID', value: '' },
        { key: 'linkCustom1Title', value: 'Services Config Generator' },
        { key: 'linkCustom1URL', value: 'https://www.divumwx.org/settingsGen/' },
        { key: 'linkCustom2Title', value: '' },
        { key: 'linkCustom2URL', value: '' },
        { key: 'USAWeatherFinder', value: '' },
        { key: 'extraLinkTitle', value: '' },
        { key: 'extraLinkColor', value: '' },
        { key: 'extraLinkURL', value: '' }
    ];
});

