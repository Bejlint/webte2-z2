<?php
require_once('../../config.php');
require_once 'objects/Laureate.class.php';
require_once 'objects/Prize.class.php';
require_once 'objects/Details.class.php';
require_once 'objects/PersonPrize.class.php';
require_once 'objects/Country.class.php';
require_once 'objects/PersonCountry.class.php';
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$db = connectToDatabase($servername, $username, $password, $dbname);
$method = $_SERVER['REQUEST_METHOD'];
$route = array_values(array_filter(explode('/', $_GET['route'])));
$prize = new Prize($db);
$laureate = new Laureate($db);
$country = new Country(($db));
$details = new Detail($db);
$person_prize = new PersonPrize($db);
$personCountry = new PersonCountry($db);

switch ($method) {
    case 'GET':


        if ($route[0] == 'prize' && count($route) == 1) { //prizes
            http_response_code(200);
            echo json_encode($prize->index());  // Get all laureates
            break;
        }
        elseif($route[0] == 'countries' && count($route) == 1) {
            http_response_code(200);
            echo json_encode($country->index());  // Get all laureates
            break;
        }
        else if($route[0] == 'details' && count($route) == 1) {
            http_response_code(200);
            echo json_encode($details->index());  // Get all laureates
            break;
        }
        else if($route[0] == 'laureates' && count($route) == 1) { //
            http_response_code(200);
            echo json_encode($laureate->index());  // Get all laureates
            break;
        }
        else if($route[0] == 'laureates'  && count($route) == 2 && is_numeric($route[1])) { //
            $id = $route[1];
            $data = $laureate->show($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
                break;
            }
        }
        elseif ($route[0] == 'prize' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $data = $prize->show($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
                break;
            }
        }
        elseif ($route[0] == 'laureates' && count($route) >= 2 && $route[1] == 'formed') {
            $laureatesData = $laureate->index();
            $allPrizes = $prize->index();
            $allCountries = $country->index();
            $result = [];

            $prizesById = [];
            foreach ($allPrizes as $p) {
                $prizesById[$p['id']] = $p;
            }

            $countriesById = [];
            foreach ($allCountries as $c) {
                $countriesById[$c['id']] = $c;
            }

            // Ha van harmadik paraméter, az ID
            $filterId = isset($route[2]) ? (int)$route[2] : null;

            if ($laureatesData) {
                foreach ($laureatesData as $laurData) {
                    if ($filterId !== null && $laurData['id'] != $filterId) {
                        continue; // Ha van ID és nem egyezik, kihagyjuk
                    }

                    $id = $laurData['id'];
                    $personPrizes = $person_prize->show($id);
                    $personCountries = $personCountry->show($id);

                    $rokValue = '';
                    $categoryValue = '';
                    if ($personPrizes && isset($personPrizes[0]) && isset($prizesById[$personPrizes[0]['prize_id']])) {
                        $prizeData = $prizesById[$personPrizes[0]['prize_id']];
                        $rokValue = $prizeData['rok'] ?? '';
                        $categoryValue = $prizeData['category'] ?? '';
                    }

                    $countryValue = '';
                    if ($personCountries && isset($personCountries[0]) && isset($countriesById[$personCountries[0]['country_id']])) {
                        $countryData = $countriesById[$personCountries[0]['country_id']];
                        $countryValue = $countryData['country_name'] ?? '';
                    }

                    $result[] = [
                        'id' => $laurData['id'],
                        'fullname' => (!isset($laurData['fullname']) || $laurData['fullname'] === null || trim($laurData['fullname']) === '')
                            ? $laurData['organisation']
                            : $laurData['fullname'],
                        'rok' => $rokValue,
                        'category' => $categoryValue,
                        'country_name' => $countryValue
                    ];
                }

                if ($filterId !== null && empty($result)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'No laureate found with the given ID']);
                } else {
                    http_response_code(200);
                    echo json_encode($result);
                }
                break;
            }
        }
        elseif ($route[0] == 'laureates' && count($route) >= 2 && $route[1] == 'modify' && is_numeric($route[2])) {
            $id = (int)$route[2];

            // Get the laureate by ID
            $laureateData = $laureate->show($id);

            if (!$laureateData) {
                http_response_code(404);
                echo json_encode(['error' => 'No laureate found with the given ID']);
                break;
            }

            // Get associated prize data
            $personPrizes = $person_prize->show($id);
            $prizeData = null;
            $rokValue = '';
            $categoryValue = '';
            $contributionSk = '';
            $contributionEn = '';
            $detailsId = null;

            if ($personPrizes && isset($personPrizes[0])) {
                $prizeId = $personPrizes[0]['prize_id'];
                $prizeData = $prize->show($prizeId);
                if ($prizeData && isset($prizeData[0])) {
                    $rokValue = $prizeData[0]['rok'] ?? '';
                    $categoryValue = $prizeData[0]['category'] ?? '';
                    $contributionSk = $prizeData[0]['contirb_sk'] ?? ''; // Corrected field name
                    $contributionEn = $prizeData[0]['contrb_en'] ?? '';  // Corrected field name
                    $detailsId = $prizeData[0]['details_id'] ?? null;
                }
            }

            // Get country data
            $personCountries = $personCountry->show($id);
            $countryValue = '';
            $countryId = null;

            if ($personCountries && isset($personCountries[0])) {
                $countryId = $personCountries[0]['country_id'];
                $countryData = $country->show($countryId);
                if ($countryData && isset($countryData[0])) {
                    $countryValue = $countryData[0]['country_name'] ?? '';
                }
            }

            // Get details data
            $languageSk = '';
            $languageEn = '';
            $genreSk = '';
            $genreEn = '';

            if ($detailsId) {
                $detailsData = $details->show($detailsId);
                if ($detailsData && isset($detailsData[0])) {
                    $languageSk = $detailsData[0]['language_sk'] ?? '';
                    $languageEn = $detailsData[0]['language_eng'] ?? '';
                    $genreSk = $detailsData[0]['genre_sk'] ?? '';
                    $genreEn = $detailsData[0]['genre_eng'] ?? '';
                }
            }

            // Construct the result
            $result = [
                'success' => true,
                'laureate' => [
                    'id' => $laureateData[0]['id'] ?? $id,
                    'fullname' => (!isset($laureateData[0]['fullname']) || $laureateData[0]['fullname'] === null || trim($laureateData[0]['fullname']) === '')
                        ? ($laureateData[0]['organisation'] ?? '')
                        : $laureateData[0]['fullname'],
                    'organisation' => $laureateData[0]['organisation'] ?? '',
                    'birth' => $laureateData[0]['birth'] ?? '',
                    'death' => $laureateData[0]['death'] ?? '',
                    'sex' => $laureateData[0]['sex'] ?? '',
                    'country' => $countryValue,
                    'country_id' => $countryId,
                    'prize_id' => $prizeId ?? null,
                    'details_id' => $detailsId,
                    'rok' => $rokValue,
                    'category' => $categoryValue,
                    'language_sk' => $languageSk,
                    'language_en' => $languageEn,
                    'genre_sk' => $genreSk,
                    'genre_en' => $genreEn,
                    'contrib_sk' => $contributionSk,
                    'contrib_en' => $contributionEn
                ]
            ];

            http_response_code(200);
            echo json_encode($result);
            break;
        }
        elseif ($route[0] == 'laureates' && count($route) == 3 && is_numeric($route[1]) && $route[2] == 'table') {
            $id = $route[1];
            $laureateData = $laureate->show($id);

            if ($laureateData) {
                $stmt = $person_prize->show($id);
                $stmt_country = $personCountry->show($id);
                $prizeData = $prize->show($stmt[0]['prize_id']);
                $countryData = $country->show($stmt_country[0]['country_id']);

                if ($prizeData) {
                    $mergedData = [
                        'fullname' => $laureateData[0]['fullname'],
                        'rok' => $prizeData[0]['rok'],
                        'category' => $prizeData[0]['category'],
                        'country_name' => $countryData[0]['country_name'],
                    ];
                    http_response_code(200);
                    echo json_encode($mergedData);
                    break;
                } else {
                    http_response_code(404);
                    echo json_encode(['message' => 'Prize not found']);
                    break;
                }
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Laureate not found']);
                break;
            }
        }

        elseif ($route[0] == 'countries' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $data = $country->show($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
                break;
            }

        } elseif ($route[0] == 'details' && count($route) == 1) { //laureates
            http_response_code(200);
            echo json_encode($details->index());
            break;
        } elseif ($route[0] == 'details' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $data = $details->show($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
                break;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Not found']);
        break;
    case 'POST':
        if ($route[0] == 'prizes' && count($route) == 1) {
            $data = json_decode(file_get_contents('php://input'), true);

            foreach ($data as $key => $value) {
                if (!isset($data[$key]) || $data[$key] == '') {
                    $data[$key] = null;
                }
            }

            $newID = $prize->store($data['sex'], $data['birth'], $data['death'], $data['fullname'], $data['organisation']);

            if (!is_numeric($newID)) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $newID]);
                break;
            }

            $new_laureate = $prize->show($newID);
            http_response_code(201);
            echo json_encode([
                'message' => "Created successfully",
                'data' => $new_laureate
            ]);
            break;

        }
        else if ($route[0] == 'laureates' && count($route) == 2 && $route[1] == 'more') {
            try {
                $payload = json_decode(file_get_contents('php://input'), true);

                if (!is_array($payload)) {
                    throw new Exception("Input JSON must be an array of laureates");
                }

                $results = [];

                foreach ($payload as $data) {
                    foreach ($data as $key => $value) {
                        if (!isset($data[$key]) || $data[$key] === '') {
                            $data[$key] = null;
                        }
                    }

                    $db->begin_transaction();

                    // Initialize IDs
                    $country_id = null;
                    $details_id = null;
                    $prize_id = null;

                    if (!empty($data['country_name'])) {
                        $country_id = $country->store($data['country_name']);
                        if (!is_numeric($country_id)) {
                            throw new Exception("Failed to create country: " . $country_id);
                        }
                    }

                    if (isset($data['language_sk'], $data['language_en'], $data['genre_sk'], $data['genre_en'])) {
                        $details_id = $details->store(
                            $data['language_sk'],
                            $data['language_en'],
                            $data['genre_sk'],
                            $data['genre_en']
                        );
                        if (!is_numeric($details_id)) {
                            throw new Exception("Failed to create details: " . $details_id);
                        }
                    }

                    if (isset($data['rok'], $data['category'], $data['contrib_sk'])) {
                        $prize_id = $prize->store(
                            $data['rok'],
                            $data['category'],
                            $data['contrib_sk'],
                            $data['contrib_en'] ?? null,
                            $details_id
                        );
                        if (!is_numeric($prize_id)) {
                            throw new Exception("Failed to create prize: " . $prize_id);
                        }
                    }

                    $newLaureateId = $laureate->store(
                        $data['sex'],
                        $data['birth'],
                        $data['death'] ?? null,
                        $data['fullname'],
                        $data['organisation'] ?? null,
                        $country_id,
                        $prize_id
                    );

                    if (!is_numeric($newLaureateId)) {
                        throw new Exception($newLaureateId);
                    }

                    $db->commit();

                    $results[] = [
                        'laureate' => $laureate->show($newLaureateId),
                        'ids' => [
                            'laureate_id' => $newLaureateId,
                            'country_id' => $country_id,
                            'prize_id' => $prize_id,
                            'details_id' => $details_id
                        ]
                    ];
                }

                http_response_code(201);
                echo json_encode([
                    'message' => "All records created successfully",
                    'count' => count($results),
                    'data' => $results
                ]);
            } catch (Exception $e) {
                if ($db->connect_errno == 0) {
                    $db->rollback();
                }

                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'error' => $e->getMessage()]);
            }

            break;
        }
        else if ($route[0] == 'laureates' && count($route) == 1) {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                foreach ($data as $key => $value) {
                    if (!isset($data[$key]) || $data[$key] == '') {
                        $data[$key] = null;
                    }
                }

                $db->begin_transaction();

                // Initialize IDs
                $country_id = null;
                $details_id = null;
                $prize_id = null;

                // Store country if provided
                if (isset($data['country_name']) && !empty($data['country_name'])) {
                    $country_id = $country->store($data['country_name']);
                    if (!is_numeric($country_id)) {
                        throw new Exception("Failed to create country: " . $country_id);
                    }
                }

                // Store details if provided
                if (isset($data['language_sk']) && isset($data['language_en']) &&
                    isset($data['genre_sk']) && isset($data['genre_en'])) {
                    $details_id = $details->store(
                        $data['language_sk'],
                        $data['language_en'],
                        $data['genre_sk'],
                        $data['genre_en']
                    );
                    if (!is_numeric($details_id)) {
                        throw new Exception("Failed to create details: " . $details_id);
                    }
                }

                // Store prize if provided
                if (isset($data['rok']) && isset($data['category']) && isset($data['contrib_sk'])) {
                    $prize_id = $prize->store(
                        $data['rok'],
                        $data['category'],
                        $data['contrib_sk'],
                        $data['contrib_en'] ?? null,
                        $details_id // Pass the details_id to link them
                    );
                    if (!is_numeric($prize_id)) {
                        throw new Exception("Failed to create prize: " . $prize_id);
                    }
                }

                // Create laureate with the related IDs
                $newLaureateId = $laureate->store(
                    $data['sex'],
                    $data['birth'],
                    $data['death'] ?? null,
                    $data['fullname'],
                    $data['organisation'] ?? null,
                    $country_id, // Pass country_id to create relationship
                    $prize_id    // Pass prize_id to create relationship
                );

                if (!is_numeric($newLaureateId)) {
                    throw new Exception($newLaureateId);
                }

                // Commit all changes
                $db->commit();

                // Get the created laureate
                $new_laureate = $laureate->show($newLaureateId);

                http_response_code(201);
                echo json_encode([
                    'message' => "Created successfully",
                    'data' => $new_laureate,
                    'ids' => [
                        'laureate_id' => $newLaureateId,
                        'country_id' => $country_id,
                        'prize_id' => $prize_id,
                        'details_id' => $details_id
                    ]
                ]);
            } catch (Exception $e) {
                // Rollback transaction on any error
                if ($db->connect_errno == 0) {
                    $db->rollback();
                }

                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'error' => $e->getMessage()]);
            }
            break;
        }
        elseif ($route[0] == 'laureates' && count($route) == 2 && is_numeric($route[1])) {
            try {
                $id = (int)$route[1];
                $data = json_decode(file_get_contents('php://input'), true);

                // Validate that the laureate exists
                $existingLaureate = $laureate->show($id);
                if (!$existingLaureate) {
                    http_response_code(404);
                    echo json_encode(['message' => 'Laureate not found']);
                    break;
                }

                // Clean up empty fields
                foreach ($data as $key => $value) {
                    if (!isset($data[$key]) || $data[$key] === '') {
                        $data[$key] = null;
                    }
                }

                $db->begin_transaction();

                // Update or get country_id
                $country_id = null;
                if (isset($data['country_id']) && !empty($data['country_id'])) {
                    $country_id = $data['country_id'];
                } elseif (isset($data['country']) && !empty($data['country'])) {
                    // Look up country by name or create it
                    $country_id = $country->update($data['country']);
                }

                // Update or get details_id
                $details_id = $data['details_id'] ?? null;
                if ((isset($data['language_sk']) || isset($data['language_en']) ||
                    isset($data['genre_sk']) || isset($data['genre_en']))) {

                    if ($details_id) {
                        // Update existing details
                        $details->update(
                            $details_id,
                            $data['language_sk'] ?? null,
                            $data['language_en'] ?? null,
                            $data['genre_sk'] ?? null,
                            $data['genre_en'] ?? null
                        );
                    } else {
                        // Create new details
                        $details_id = $details->update(
                            $data['language_sk'] ?? null,
                            $data['language_en'] ?? null,
                            $data['genre_sk'] ?? null,
                            $data['genre_en'] ?? null
                        );
                    }
                }

                // Update or get prize_id
                $prize_id = $data['prize_id'] ?? null;
                if ((isset($data['rok']) || isset($data['category']) ||
                    isset($data['contirb_sk']) || isset($data['contib_en']))) {

                    if ($prize_id) {
                        // Update existing prize
                        $prize->update(
                            $prize_id,
                            $data['rok'] ?? null,
                            $data['category'] ?? null,
                            $data['contirb_sk'] ?? null,
                            $data['contrib_en'] ?? null,
                            $details_id
                        );
                    } else {
                        // Create new prize
                        $prize_id = $prize->update(
                            $data['rok'] ?? null,
                            $data['category'] ?? null,
                            $data['contirb_sk'] ?? null,
                            $data['contib_en'] ?? null,
                            $details_id
                        );
                    }
                }

                // Update laureate
                $updateResult = $laureate->update(
                    $id,
                    $data['sex'] ?? null,
                    $data['born'] ?? null,
                    $data['died'] ?? null,
                    $data['fullname'] ?? null,
                    $data['organisation'] ?? null
                );

                if ($updateResult !== 0) {
                    throw new Exception("Failed to update laureate: " . $updateResult);
                }

                // Update the person-prize association if we have a prize_id
                if ($prize_id) {
                    // Check if the association already exists
                    $existingPrizes = $person_prize->show($id);
                    if (empty($existingPrizes)) {
                        $person_prize->update($id, $prize_id);
                    } else {
                        // Update existing association
                        $person_prize->update($id, $prize_id);
                    }
                }

                // Update the person-country association if we have a country_id
                if ($country_id) {
                    // Check if the association already exists
                    $existingCountries = $personCountry->show($id);
                    if (empty($existingCountries)) {
                        $personCountry->update($id, $country_id);
                    } else {
                        // Update existing association
                        $personCountry->update($id, $country_id);
                    }
                }

                // Commit all changes
                $db->commit();

                // Get the updated laureate with all relationships
                $updatedLaureate = $laureate->show($id);

                // Get associated data
                $personPrizes = $person_prize->show($id);
                $prizeInfo = null;
                if (!empty($personPrizes)) {
                    $prizeInfo = $prize->show($personPrizes[0]['prize_id']);
                }

                $personCountries = $personCountry->show($id);
                $countryInfo = null;
                if (!empty($personCountries)) {
                    $countryInfo = $country->show($personCountries[0]['country_id']);
                }

                $detailsInfo = null;
                if (isset($prizeInfo[0]['details_id']) && $prizeInfo[0]['details_id']) {
                    $detailsInfo = $details->show($prizeInfo[0]['details_id']);
                }

                // Construct response
                $response = [
                    'message' => "Updated successfully",
                    'data' => [
                        'laureate' => $updatedLaureate[0] ?? null,
                        'prize' => $prizeInfo[0] ?? null,
                        'country' => $countryInfo[0] ?? null,
                        'details' => $detailsInfo[0] ?? null
                    ]
                ];

                http_response_code(200);
                echo json_encode($response);

            } catch (Exception $e) {
                // Rollback transaction on any error
                if ($db->connect_errno == 0) {
                    $db->rollback();
                }

                http_response_code(400);
                echo json_encode(['message' => "Failed to update laureate", 'error' => $e->getMessage()]);
            }
            break;
        }
        elseif ($route[0] == 'countries' && count($route) == 1) {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!empty($data['country_name'])) {
                $country_id = $country->store($data['country_name']);
                if (!is_numeric($country_id)) {
                    throw new Exception("Failed to create country: " . $country_id);
                }
            }
            if (!is_numeric($country_id)) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $country_id]);
                break;
            }

            $new_laureate = $laureate->show($country_id);
            http_response_code(201);
            echo json_encode([
                'message' => "Created successfully",
                'data' => $new_laureate
            ]);
            break;

        }
        elseif ($route[0] == 'details' && count($route) == 1) {
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['language_sk'], $data['language_eng'], $data['genre_sk'], $data['genre_eng'])) {
                $details_id = $details->store(
                    $data['language_sk'],
                    $data['language_en'],
                    $data['genre_sk'],
                    $data['genre_en']
                );
                if (!is_numeric($details_id)) {
                    throw new Exception("Failed to create details: " . $details_id);
                }
                if (!is_numeric($details_id)) {
                    http_response_code(400);
                    echo json_encode(['message' => "Bad request", 'data' => $details_id]);
                    break;
                }
            }


            http_response_code(201);
            echo json_encode([
                'message' => "Created successfully"
            ]);
            break;

        }


        http_response_code(400);
        echo json_encode(['message' => 'Bad request ']);
        break;
    case'PUT':
        if ($route[0] == 'prize' && count($route) == 2 && is_numeric($route[1])) {
            $currentID = $route[1];
            $currentData = $prize->show($currentID);
            if (!$currentData) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $updatedData = json_decode(file_get_contents('php://input'), true);
            $currentData = array_merge($currentData, $updatedData);

            $status = $prize->update(
                $route[0],
                $currentData['rok'],
                $currentData['category'],
                $currentData['contirb_sk'],
                $currentData['contrb_en'],
            );

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode([
                'message' => "Updated successfully",
                'data' => $currentData
            ]);
            break;
        }
        elseif ($route[0] == 'laureates' && count($route) == 1) {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                foreach ($data as $key => $value) {
                    if (!isset($data[$key]) || $data[$key] == '') {
                        $data[$key] = null;
                    }
                }

                $db->begin_transaction();

                if (isset($data['country_name']) && !empty($data['country_name'])) {
                    $country_id = $country->store($data['country_name']);
                    var_dump($country_id);
                }
                if ((isset($data['language_sk']) && (isset($data['language_en'])) && (isset($data['genre_sk'])) && (isset($data['genre_en'])))) {
                    $details_id = $details->store($data['language_sk'], $data['language_en'], $data['genre_sk'], $data['genre_en']);
                }

                if ((isset($data['rok']) && (isset($data['category'])) && (isset($data['contrib_sk'])))) {
                    $prize_id = $prize->store($data['rok'], $data['category'], $data['contrib_sk'], $data['contrib_en'], $details_id);
                }

                $newLaureateId = $laureate->store(
                    $data['sex'],
                    $data['birth'],
                    $data['death'] ?? null,
                    $data['fullname'],
                    $data['organisation'],
                    $country_id,
                    $prize_id,
                );

                if (!is_numeric($newLaureateId)) {
                    throw new Exception($newLaureateId);
                }

                // Commit all changes
                $db->commit();

                // Get the created laureate with all relationships
                $new_laureate = $laureate->show($newLaureateId);

                // Add country and prize information to response
                if ($country_id) {
                    $countryInfo = $country->show($country_id);
                    if ($countryInfo) {
                        $new_laureate[0]['country'] = $countryInfo[0];
                    }
                }

                if ($prize_id) {
                    $prizeInfo = $prize->show($prize_id);
                    if ($prizeInfo) {
                        $new_laureate[0]['prize'] = $prizeInfo[0];
                    }
                }

                http_response_code(201);
                echo json_encode([
                    'message' => "Created successfully",
                    'data' => $new_laureate
                ]);
            } catch (Exception $e) {
                // Rollback transaction on any error
                if ($db->connect_errno == 0) {
                    $db->rollback();
                }

                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'error' => $e->getMessage()]);
            }
            break;
        }
        elseif($route[0] == 'countries' && is_numeric($route[1]) &&  count($route) == 2){
            $currentData = json_decode(file_get_contents('php://input'), true);
            $status = $country->update(
                $route[1],
                $currentData['country_name']
            );

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode([
                'message' => "Updated successfully",
                'data' => $currentData
            ]);
            break;
        }
        elseif($route[0] == 'laureates' && count($route) == 2 && is_numeric($route[1])){
            $currentID = $route[1];
            $currentData = $laureate->show($currentID);
            if (!$currentData) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $updatedData = json_decode(file_get_contents('php://input'), true);
            $currentData = array_merge($currentData, $updatedData);

            $status = $laureate->update(
                $currentID,
                $currentData['sex'],
                $currentData['birth'],
                $currentData['death'],
                !empty($currentData['fullname']) ? $currentData['fullname'] : $currentData['organisation']
            );


            if ($status == 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode([
                'message' => "Updated successfully",
                'data' => $currentData
            ]);
            break;
        }
        elseif ($route[0] == 'countries' && count($route) == 2 && is_numeric($route[1])) {
            $currentID = $route[1];
            $currentData = $laureate->show($currentID);
            if (!$currentData) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $updatedData = json_decode(file_get_contents('php://input'), true);
            $currentData = array_merge($currentData, $updatedData);

            $status = $laureate->update(
                $currentID,
                $currentData['gender'],
                $currentData['birth'],
                $currentData['death'],
                $currentData['fullname'],
                $currentData['organisation']
            );

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode([
                'message' => "Updated successfully",
                'data' => $currentData
            ]);
            break;
        }
        elseif ($route[0] == 'details' && count($route) == 2 && is_numeric($route[1])) {
            $currentID = $route[1];
            $currentData = $details->show($currentID);
            if (!$currentData) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $updatedData = json_decode(file_get_contents('php://input'), true);
            $currentData = array_merge($currentData, $updatedData);

            $status = $details->update(
                $currentID,
                $currentData['gender'],
                $currentData['birth'],
                $currentData['death'],
                $currentData['fullname'],
                $currentData['organisation']
            );

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode([
                'message' => "Updated successfully",
                'data' => $currentData
            ]);
            break;
        }
        elseif ($route[0] == 'laureates' && count($route) == 3 && is_numeric($route[2]) && $route[1] == 'modify') {
            try {
                $id = (int)$route[2];
                $data = json_decode(file_get_contents('php://input'), true);

                // Validate that the laureate exists
                $existingLaureate = $laureate->show($id);

                $existingLaureate = $laureate->show($id);
                if (!$existingLaureate) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Laureate not found']);
                    break;
                }

                $db->begin_transaction();


                $updateResult = '';
                if (isset($data['fullname']) || isset($data['organisation']) ||
                    isset($data['sex']) || isset($data['birth']) || isset($data['death'])) {

                    $updateResult = $laureate->update(
                        $id,
                        $data['sex'] ?? $existingLaureate[0]['sex'] ?? null,
                        $data['birth'] ?? $existingLaureate[0]['birth'] ?? null,
                        $data['death'] ?? $existingLaureate[0]['death'] ?? null,
                        $data['fullname'] ?? $existingLaureate[0]['fullname'] ?? null,
                        $data['organisation'] ?? $existingLaureate[0]['organisation'] ?? null
                    );
                    if (is_array($updateResult) || (is_string($updateResult) && strpos($updateResult, 'successfully') !== false)) {
                    } elseif ($updateResult !== 0) {
                        throw new Exception("Failed to update laureate data: " . json_encode($updateResult));
                    }
                }
                $prize_id = null;
                $country_id = null;

                if (isset($data['country']) && !empty($data['country'])) {


                    $existingCountryQuery = $country->findByName($data['country']);
                    if ($existingCountryQuery && !empty($existingCountryQuery)) {
                        $country_id = $existingCountryQuery[0]['id'];


                        $resultUCP = $country->updateCountryPrize($data['id'], $country_id);

                    } else {

                        $country_id = $country->store($data['country']);
                        $country->updateCountryPrize($data['id'], $country_id);
                    }


                }

                // Process prize data
                if ((isset($data['rok']) || isset($data['category']))) {
                    $prize_id = null;

                    $prize_id = $prize->findById($id);
                    $prizeData = $prize->show($prize_id[0]['prize_id']);
                    if ($prizeData) {

                        $prize->update(
                            $prize_id[0]['prize_id'],
                            $data['rok'] ?? $prizeData[0]['rok'],
                            $data['category'] ?? $prizeData[0]['category'],
                             $data['contrib_sk'] ?? $prizeData[0]['contirb_sk'] ,
                             $data['contrib_en'] ?? $prizeData[0]['contrb_en'],
                        );

                    } else {
                        // Create new prize
                        $prize_id = $prize->store(
                            $data['rok'] ?? null,
                            $data['category'] ?? null,
                            $data['contirb_sk'] ?? null,
                            $data['contrb_en'] ?? null,
                            null
                        );

                    }
                }


                if ((isset($data['language_sk']) || isset($data['genre_sk']))) {
                    $prize_id = null;

                    $prize_id = $prize->findById($id);
                    $prizeData = $prize->findDetailByPrizeId($prize_id[0]['prize_id']);
                    if ($prizeData) {

                        $details->update(
                            $prizeData[0]['details_id'],
                            $data['language_sk'] ?? $prizeData[0]['language_sk'],

                            $data['genre_sk'] ?? $prizeData[0]['genre_sk'] ,
                            $data['language_en'] ?? $prizeData[0]['language_en'],
                            $data['genre_en'] ?? $prizeData[0]['genre_en'],
                        );

                    } else {
                        $prize_id = $prize->store(
                            $data['language_sk'] ?? null,
                            $data['language_en'] ?? null,
                            $data['genre_sk'] ?? null,
                            $data['genre_en'] ?? null,
                            null
                        );

                    }
                }
                $db->commit();

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Laureate updated successfully'
                ]);

            } catch (Exception $e) {
                // Rollback transaction on any error
                if ($db->connect_errno == 0) {
                    $db->rollback();
                }

                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update laureate',
                    'error' => $e->getMessage()
                ]);
            }
            break;
        }

        http_response_code(404);
        echo json_encode(['message' => 'Not found']);
        break;
    case 'DELETE':
        if ($route[0] == 'prize' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $exist = $prize->show($id);
            if (!$exist) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $status = $prize->destroy($id);

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode(['message' => "Deleted successfully"]);
            break;

        }
        elseif ($route[0] == 'laureates' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $exist = $laureate->show($id);
            if (!$exist) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $result = $laureate->destroy($id);

            // Check if the result is an array with a success message
            if (is_array($result) && isset($result['message'])) {
                http_response_code(200);
                echo json_encode($result);
                break;
            } else if ($result !== 0) {
                // If it's not a success array or 0, it's an error
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $result]);
                break;
            }

            http_response_code(200);
            echo json_encode(['message' => "Deleted successfully"]);
            break;

            http_response_code(201);
            echo json_encode(['message' => "Deleted successfully"]);
            break;

        }
        elseif ($route[0] == 'countries' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $exist = $country->show($id);
            if (!$exist) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $status = $country->destroy($id);

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode(['message' => "Deleted successfully"]);
            break;

        }
        elseif ($route[0] == 'details' && count($route) == 2 && is_numeric($route[1])) {
            $id = $route[1];
            $exist = $details->show($id);
            if (!$exist) {
                http_response_code(404);
                echo json_encode(['message' => 'Not found']);
                break;
            }

            $status = $details->destroy($id);

            if ($status != 0) {
                http_response_code(400);
                echo json_encode(['message' => "Bad request", 'data' => $status]);
                break;
            }

            http_response_code(201);
            echo json_encode(['message' => "Deleted successfully"]);
            break;

        }

        http_response_code(404);
        echo json_encode(['message' => 'Not found']);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
