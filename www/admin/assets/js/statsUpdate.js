//##############################################################################################
//#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
//#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
//#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
//#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
//#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
//#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
//#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
//#                                                                                            #
//#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
//#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
//#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
//#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
//##############################################################################################
var isFirstRun = true;

function fetchStats() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                displayStats(response);
                updateTimestamp(response);
                if (isFirstRun) {
                    updateUpTime(response.sysUptime);
                    updateOsSystem(response.osSystem);
                    updateLastReboot(response.rebootTime);
                    isFirstRun = false;
                }
            } else {
                console.error("Error: " + xhr.statusText);
            }
        }
    };
    xhr.open("GET", "./srvStats.php", true);
    xhr.send();
}

function displayStats(data) {
    var cpuContainer = document.getElementById("cpuContainer");
    var memContainer = document.getElementById("memContainer");
    var tableHTML = "<table><thead><tr><th class='small' style='width: 50px;'>CPU</th><th class='small' style='width: 50px;'>In Use</th><th class='small' style='width: 50px;'>Idle</th></tr></thead><tbody>";

    for (var cpu in data) {
        if (data.hasOwnProperty(cpu) && cpu !== "timestamp" && cpu !== "memory" && cpu !== "sysUptime" && cpu !== "osSystem" && cpu !== "rebootTime") {
            var stats = data[cpu];
            var inUse = parseFloat(stats.usr) + parseFloat(stats.nice) + parseFloat(stats.sys) + parseFloat(stats.iowait) + parseFloat(stats.irq) + parseFloat(stats.soft) + parseFloat(stats.steal) + parseFloat(stats.guest) + parseFloat(stats.gnice);
            var idle = parseFloat(stats.idle);

            tableHTML += "<tr><td>" + cpu + "</td><td>" + inUse.toFixed(2) + "%</td><td>" + idle.toFixed(2) + "%</td></tr>";
        }
    }
    tableHTML += "</tbody></table>";
    cpuContainer.innerHTML = tableHTML;

    var memData = data.memory;
    var totalMem = parseFloat(memData.MemTotal) / 1024;
    var totalMemFree = parseFloat(memData.MemFree) / 1024;
    var totalMemUsed = (parseFloat(memData.MemTotal) - parseFloat(memData.MemFree)) / 1024;
    var cachedMem = (parseFloat(memData.Cached) + parseFloat(memData.SReclaimable) - parseFloat(memData.Shmem)) / 1024;
    var swap = (parseFloat(memData.SwapTotal) - parseFloat(memData.SwapFree)) / 1024;

    var memHTML = "<span class='mem-label'>Total Mem:</span> <span class='mem-value'>" + totalMem.toFixed(2) + " MB</span><br />";
    memHTML += "<span class='mem-label'>Free Mem:</span> <span class='mem-value'>" + totalMemFree.toFixed(2) + " MB</span><br />";
    memHTML += "<span class='mem-label'>Mem Used:</span> <span class='mem-value'>" + totalMemUsed.toFixed(2) + " MB</span><br />";
    memHTML += "<span class='mem-label'>Buffers:</span> <span class='mem-value'>" + (parseFloat(memData.Buffers) / 1024).toFixed(2) + " MB</span><br />";
    memHTML += "<span class='mem-label'>Cached Mem:</span> <span class='mem-value'>" + cachedMem.toFixed(2) + " MB</span><br />";
    memHTML += "<span class='mem-label'>Swap:</span> <span class='mem-value'>" + swap.toFixed(2) + " MB</span><br />";
    memContainer.innerHTML = memHTML;
}

function updateTimestamp(data) {
    var timestamp = data.timestamp;
    var cpuUpdateElement = document.getElementById("cpuUpdate");
    var memUpdateElement = document.getElementById("memUpdate");
    cpuUpdateElement.textContent = "Updated: " + timestamp;
    memUpdateElement.textContent = "Updated: " + timestamp;
}

function updateUpTime(upTime) {
    var upTimeContainer = document.getElementById("upTime");
    upTimeContainer.textContent = "Uptime: " + upTime;
}

function updateOsSystem(osSystem) {
    var osSystemContainer = document.getElementById("osSystem");
    osSystemContainer.textContent = "OS System: " + osSystem;
}

function updateLastReboot(rebootTime) {
    var osSystemContainer = document.getElementById("rebootTime");
    osSystemContainer.textContent = "Last Rebooted: " + rebootTime;
}

fetchStats();
setInterval(fetchStats, 1500);
