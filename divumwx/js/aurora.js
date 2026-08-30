// Simple synchronous version (if you're loading the XML separately)
function parseLatestAuroraValue(xmlString) {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlString, 'text/xml');
    const activities = xmlDoc.getElementsByTagName('activity');
    const lastActivity = activities[activities.length - 1];
    const value = lastActivity.getElementsByTagName('value')[0].textContent;
    return parseFloat(value);
}

// Usage in HTML:
// <script>
// fetch('./jsondata/aurora.txt')
//     .then(response => response.text())
//     .then(xml => {
//         const latestValue = parseLatestAuroraValue(xml);
//         document.getElementById('aurora-value').textContent = latestValue;
//     });
// </script>