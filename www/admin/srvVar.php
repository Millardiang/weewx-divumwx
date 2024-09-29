<?php
echo '<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    tr:nth-child(even) {background-color: #f2f2f2;}
    th {
        background-color: #4CAF50;
        color: white;
    }
</style>';

echo "<table>";
echo "<tr><th>Server Variable</th><th>Value</th></tr>";

foreach ($_SERVER as $key => $value) {
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    echo "<tr>";
    echo "<td>" . htmlspecialchars($key) . "</td>";
    echo "<td>" . htmlspecialchars($value) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>

