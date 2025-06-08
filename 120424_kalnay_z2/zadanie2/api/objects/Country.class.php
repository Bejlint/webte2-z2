<?php

use OpenApi\Annotations AS OA;

/**
 * @OA\Info(
 *     title="Nobel Prize Countries API",
 *     version="1.0",
 *     description="API for managing countries associated with Nobel Prize laureates",
 *     @OA\Contact(
 *         email="admin@example.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Components(
 *     @OA\Schema(
 *         schema="country",
 *         type="object",
 *         required={"id", "country_name"},
 *         @OA\Property(
 *             property="id",
 *             type="integer",
 *             description="Unique identifier for the country",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="country_name",
 *             type="string",
 *             description="Name of the country",
 *             example="Sweden"
 *         )
 *     )
 * )
 */

/**
 * @OA\Tag(
 *     name="country",
 *     description="Operations related to countries"
 * )
 */
class Country
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @OA\Get(
     *     path="/country",
     *     tags={"country"},
     *     summary="Get all countries",
     *     description="Returns a list of all countries in the database",
     *     operationId="Countryindex",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/country")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM countries");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            return $data;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Get a specific country by ID.
     *
     * @OA\Get(
     *     path="/country/{id}",
     *     tags={"country"},
     *     summary="Get a single country by ID",
     *     description="Returns details for a specific country identified by its ID",
     *     operationId="Countryshow",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the country to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/country")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Country not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Country not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     *
     * @param int $id The country ID
     * @return array Country data
     */
    public function show($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM countries WHERE id = ?");
        $stmt->bind_param('i', $id);

        try {
            $stmt->execute();
            $stmt = $stmt->get_result();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Find a country by its name.
     *
     * @OA\Get(
     *     path="/country/name/{name}",
     *     tags={"country"},
     *     summary="Find country by name",
     *     description="Returns a country that matches the provided name",
     *     operationId="CountryfindByName",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="Name of the country to search for",
     *         required=true,
     *         @OA\Schema(type="string", example="Sweden")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/country")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Country not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No countries found with that name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     *
     * @param string $name The country name to search for
     * @return array Country data
     */
    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM countries WHERE country_name = ?");
        $stmt->bind_param('s', $name);

        try {
            $stmt->execute();
            $stmt = $stmt->get_result();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update an existing country by ID.
     *
     * @OA\Put(
     *     path="/country/{id}",
     *     tags={"country"},
     *     summary="Update a country",
     *     description="Updates the details of an existing country",
     *     operationId="Countryupdate",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the country to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Country data to update",
     *         @OA\JsonContent(
     *             required={"country_name"},
     *             @OA\Property(
     *                 property="country_name",
     *                 type="string",
     *                 example="Norway",
     *                 description="New name for the country"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Country updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Country not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Country not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     *
     * @param int $id The country ID to update
     * @param string $country_name The new country name
     * @return int ID of the updated country
     */
    public function update($id, $country_name)
    {
        $stmt = $this->db->prepare("UPDATE countries SET country_name = ? WHERE id = ?");
        $stmt->bind_param('si', $country_name, $id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

        return $this->db->insert_id;
    }

    /**
     * Update the country assigned to a person.
     *
     * @OA\Put(
     *     path="/country/person/update",
     *     tags={"country", "person"},
     *     summary="Update the country for a person",
     *     description="Updates the country association for a specified person",
     *     operationId="CountryupdateCountryPrize",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Association data to update",
     *         @OA\JsonContent(
     *             required={"person_id", "country_id"},
     *             @OA\Property(
     *                 property="person_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the person"
     *             ),
     *             @OA\Property(
     *                 property="country_id",
     *                 type="integer",
     *                 example=2,
     *                 description="New country ID to associate with the person"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country association updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="affected_rows", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Country association updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Person or association not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Person or association not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     *
     * @param int $id The person ID
     * @param int $newId The new country ID
     * @return int Number of affected rows
     */
    public function updateCountryPrize($id, $newId)
    {
        $stmt = $this->db->prepare("UPDATE person_country SET country_id = ? WHERE person_id = ?");
        if (!$stmt) {
            return "Error: " . $this->db->error;
        }
        $id = (int)$id;
        $stmt->bind_param('ii', $newId, $id);

        try {
            $result = $stmt->execute();
            if (!$result) {
                return "Error: " . $stmt->error;
            }

            return $stmt->affected_rows;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        } finally {
            $stmt->close();
        }
    }

    /**
     * Create a new country.
     *
     * @OA\Post(
     *     path="/country",
     *     tags={"country"},
     *     summary="Create a new country",
     *     description="Adds a new country to the database",
     *     operationId="Countrystore",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Country data to add",
     *         @OA\JsonContent(
     *             required={"country_name"},
     *             @OA\Property(
     *                 property="country_name",
     *                 type="string",
     *                 example="Denmark",
     *                 description="Name of the country to add"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Country created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=5),
     *             @OA\Property(property="country_name", type="string", example="Denmark"),
     *             @OA\Property(property="message", type="string", example="Country created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     *
     * @param string $country_name The name of the country to create
     * @return int ID of the newly created country
     */
    public function store($country_name)
    {
        $stmt = $this->db->prepare("INSERT INTO countries(country_name) VALUES (?)");
        $stmt->bind_param('s', $country_name);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

        return $this->db->insert_id;
    }

    /**
     * Delete a country by ID.
     *
     * @OA\Delete(
     *     path="/country/{id}",
     *     tags={"country"},
     *     summary="Delete a country",
     *     description="Deletes a country from the database",
     *     operationId="Countrydestroy",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the country to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Country deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Country not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Country not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     )
     * )
     *
     * @param int $id The ID of the country to delete
     * @return int Result code (0 for success)
     */
    public function destroy($id)
    {
        $stmt = $this->db->prepare("DELETE FROM countries WHERE id = ?");
        $stmt->bind_param('i', $id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

        return 0;
    }
}
