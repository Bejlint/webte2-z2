<?php


use OpenApi\Annotations as OA;


/**
 * @OA\Info(
 *     title="Nobel Laureates API",
 *     version="1.0",
 *     description="API for managing Nobel Prize laureates"
 * )
 */

/**
 * @OA\Schema(
 *     schema="laureate",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="fullname", type="string", example="Marie Curie"),
 *     @OA\Property(property="organisation", type="string", example="University of Paris"),
 *     @OA\Property(property="sex", type="string", example="f"),
 *     @OA\Property(property="birth", type="string", example="1867"),
 *     @OA\Property(property="death", type="string", example="1934")
 * )
 */
class Laureate
{
    private $db;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    /**
     * Get all laureates.
     *
     * @OA\Get(
     *     path="/laureate",
     *     tags={"laureate"},
     *     summary="Get all laureates",
     *     description="Returns a list of all laureates in the database",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/laureate")
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
            $stmt = $this->db->prepare("SELECT * FROM person");
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
     * Get a laureate by ID.
     *
     * @OA\Get(
     *     path="/laureate/{id}",
     *     tags={"laureate"},
     *     summary="Find laureate by ID",
     *     description="Returns a single laureate by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of laureate to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/laureate")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Laureate not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     *
     * @param int $id the laureate id
     */

    public function show($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM person WHERE id = ?");
        $stmt->bind_param('i', $id);

        try {
            $stmt->execute();
            $stmt = $stmt->get_result();
            return $stmt->fetch_all(MYSQLI_ASSOC);


        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Add a new laureate.
     *
     * @OA\Post(
     *     path="/laureate",
     *     tags={"laureate"},
     *     summary="Add a new laureate",
     *     description="Create a new laureate entry in the database with associated countries and prizes",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sex", "birth"},
     *             @OA\Property(property="sex", type="string", example="m", description="Gender of the laureate (m/f)"),
     *             @OA\Property(property="birth", type="string", example="1970", description="Year of birth"),
     *             @OA\Property(property="death", type="string", example="2020", description="Year of death (if applicable)"),
     *             @OA\Property(property="fullname", type="string", example="John Doe", description="Full name of the laureate"),
     *             @OA\Property(property="organisation", type="string", example="Nobel Foundation", description="Organization the laureate belongs to"),
     *             @OA\Property(property="country_id", type="integer", example=1, description="ID of the country associated with the laureate"),
     *             @OA\Property(property="prize_id", type="integer", example=1, description="ID of the prize awarded to the laureate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Laureate successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Laureate created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Invalid data")
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





    public function store($gender, $birth, $death, $fullname = null, $organisation = null, $country_id = null, $prize_id = null)
    {
        try {
            // Start transaction
            $this->db->begin_transaction();

            // Insert the person first
            $stmt = $this->db->prepare("INSERT INTO person(fullname, organisation, sex, birth, death) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $fullname, $organisation, $gender, $birth, $death);
            $stmt->execute();

            $person_id = $this->db->insert_id;

            // Associate with country if provided
            if ($country_id !== null && is_numeric($country_id)) {
                $stmt = $this->db->prepare("INSERT INTO person_country (person_id, country_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $person_id, $country_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to associate country: " . $stmt->error);
                }
            }

            // Associate with prize if provided
            if ($prize_id !== null && is_numeric($prize_id)) {
                $stmt = $this->db->prepare("INSERT INTO person_prize (person_id, prize_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $person_id, $prize_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to associate prize: " . $stmt->error);
                }
            }

            // Commit the transaction
            $this->db->commit();

            return $person_id;
        } catch (Exception $e) {
            // Rollback in case of error
            $this->db->rollback();
            return "Error: " . $e->getMessage();
        }
    }
    /**
     * Update an existing laureate.
     *
     * @OA\Put(
     *     path="/laureate/{id}",
     *     tags={"laureate"},
     *     summary="Update an existing laureate",
     *     description="Update an existing laureate entry in the database",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of laureate to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sex", "birth"},
     *             @OA\Property(property="sex", type="string", example="m", description="Gender of the laureate"),
     *             @OA\Property(property="birth", type="string", example="1970", description="Year of birth"),
     *             @OA\Property(property="death", type="string", example="2020", description="Year of death"),
     *             @OA\Property(property="fullname", type="string", example="John Doe", description="Full name of the laureate"),
     *             @OA\Property(property="organisation", type="string", example="Nobel Foundation", description="Organization the laureate belongs to")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laureate successfully updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(property="person_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or laureate not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Laureate not found or no changes made")
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
    public function update($id, $gender, $birth, $death, $fullname = null, $organisation = null)
    {
        $stmt = $this->db->prepare("UPDATE person SET fullname = ?, organisation = ?, sex = ?, birth = ?, death = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $fullname, $organisation, $gender, $birth, $death, $id);
        $stmt2 = $this->db->prepare("SELECT country_id from person_country WHERE person_id = ?");
        $stmt2->bind_param('i', $id);

        try {
            $stmt->execute();
            $stmt2->execute();

            // Get the result and check if it has rows
            $result = $stmt2->get_result();
            if ($result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                return ["message" => "Laureate not found or no changes made"];
            }
        } catch (Exception $e) {
            return ["message" => "Error: " . $e->getMessage()];
        }
    }

    /**
     * Delete a laureate.
     *
     * @OA\Delete(
     *     path="/laureate/{id}",
     *     tags={"laureate"},
     *     summary="Delete a laureate",
     *     description="Delete a laureate entry from the database, including related records in person_country and person_prize tables",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of laureate to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laureate successfully deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Laureate successfully deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error during deletion",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error: Database error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Laureate not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Laureate not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            // Start transaction for consistent delete operation
            $this->db->begin_transaction();

            // First delete related records from person_country
            $stmt = $this->db->prepare("DELETE FROM person_country WHERE person_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            // Also delete related records from person_prize
            $stmt = $this->db->prepare("DELETE FROM person_prize WHERE person_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            // Then delete the person
            $stmt = $this->db->prepare("DELETE FROM person WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();

            // Commit transaction
            $this->db->commit();

            return ["message" => "Laureate successfully deleted"];
        } catch (Exception $e) {
            // Rollback in case of error
            $this->db->rollback();
            return ["message" => "Error: " . $e->getMessage()];
        }
    }
}