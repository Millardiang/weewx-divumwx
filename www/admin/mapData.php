<?php
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3.  See the file LICENSE.txt for your rights.      #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################

    try {
        $adminDB = '.' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'dvmAdmin.db3';
        $db = new PDO("sqlite:" . $adminDB);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $db->prepare("SELECT v.*, c.countryName FROM userVisits v LEFT JOIN countries c ON v.countryCode = c.countryCode");
        $query->execute();
        $visits = $query->fetchAll(PDO::FETCH_ASSOC);
        $visitData = array();
        $totalVisits = 0;
        foreach ($visits as $visit) {
            $country = $visit['countryName'];
            $region = $visit['regionName'];
            $city = $visit['cityName'];
            $visit_count = $visit['visit_count'];
            $lat = $visit['lat'];
            $long = $visit['long'];
            if (!isset($visitData[$country])) {
                $visitData[$country]['visit_count'] = 0;
            }
            $visitData[$country]['visit_count'] += $visit_count;
            if ($region !== 'Unknown') {
                if (!isset($visitData[$country]['regions'][$region])) {
                    $visitData[$country]['regions'][$region]['visit_count'] = 0;
                }
                $visitData[$country]['regions'][$region]['visit_count'] += $visit_count;
            }
            if ($city !== 'Unknown') {
                if (!isset($visitData[$country]['regions'][$region]['cities'][$city])) {
                    $visitData[$country]['regions'][$region]['cities'][$city] = array(
                        'visit_count' => 0,
                        'latLng' => [$lat, $long]
                    );
                }
                $visitData[$country]['regions'][$region]['cities'][$city]['visit_count'] += $visit_count;
            }
            $totalVisits += $visit_count;
        }
        $formattedData = array();
        foreach ($visitData as $country => $countryData) {
            $countryEntry = array(
                'name' => $country,
                'visit_count' => $countryData['visit_count']
            );
            $regions = array();
            if (isset($countryData['regions'])) {
                foreach ($countryData['regions'] as $region => $regionData) {
                    $regionEntry = array(
                        'name' => $region,
                        'visit_count' => $regionData['visit_count']
                    );
                    $cities = array();
                    if (isset($regionData['cities'])) {
                        foreach ($regionData['cities'] as $city => $cityData) {
                            $cities[] = array(
                                'name' => $city,
                                'visit_count' => $cityData['visit_count'],
                                'latLng' => $cityData['latLng']
                            );
                        }
                    }
                    $regionEntry['cities'] = $cities;
                    $regions[] = $regionEntry;
                }
            }
            $countryEntry['regions'] = $regions;
            $formattedData[] = $countryEntry;
        }
        $output = array(
            'total_visits' => $totalVisits,
            'data' => $formattedData
        );
        header('Content-Type: application/json');
        echo json_encode($output, JSON_PRETTY_PRINT);
        $db = null;
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo "Database error: " . $e->getMessage();
        $db = null;
        exit;
    }