<?php


use OpenApi\Annotations AS OA;

/**
 * @OA\Info(
 *     title="Nobel Prize API",
 *     version="1.0",
 *     description="API for managing Nobel Prizes and their details",
 *     @OA\Contact(
 *         email="admin@example.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="prize",
 *     type="object",
 *     required={"id", "rok", "category"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier for the prize",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="rok",
 *         type="string",
 *         description="Year the prize was awarded",
 *         example="2021"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="string",
 *         description="Category of the Nobel Prize",
 *         example="Physics"
 *     ),
 *     @OA\Property(
 *         property="contirb_sk",
 *         type="string",
 *         description="Contribution description in Slovak",
 *         example="Za objav kvantového javu"
 *     ),
 *     @OA\Property(
 *         property="contrb_en",
 *         type="string",
 *         description="Contribution description in English",
 *         example="For discovery of quantum phenomenon"
 *     ),
 *     @OA\Property(
 *         property="details_id",
 *         type="integer",
 *         description="ID of the detailed information record",
 *         example=5
 *     )
 * )
 */

class Prize
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
     * Get all prizes.
     *
     * @OA\Get(
     *     path="/prize",
     *     tags={"prize"},
     *     summary="Get all prizes",
     *     description="Returns a list of all Nobel prizes in the database",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/prize")
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
            $stmt = $this->db->prepare("SELECT * FROM prize");
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
     * Get a prize by ID.
     *
     * @OA\Get(
     *     path="/prize/{id}",
     *     tags={"prize"},
     *     summary="Get a single prize by ID",
     *     description="Returns details for a specific Nobel prize identified by its ID",
     *     operationId="show",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the prize to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/prize")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prize not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Prize not found")
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
     * @param int $id The prize ID
     * @return array Prize data
     */
    public function show($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM prize WHERE id = ?");
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
     * Update an existing prize.
     *
     * @OA\Put(
     *     path="/prize/{id}",
     *     tags={"prize"},
     *     summary="Update an existing prize",
     *     description="Updates the details of an existing Nobel prize",
     *     operationId="update",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the prize to update",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Prize data to update",
     *         @OA\JsonContent(
     *             required={"rok", "category"},
     *             @OA\Property(property="rok", type="string", example="2022", description="Year the prize was awarded"),
     *             @OA\Property(property="category", type="string", example="Chemistry", description="Category of the Nobel Prize"),
     *             @OA\Property(property="contirb_sk", type="string", example="Za vývoj novej metódy", description="Contribution in Slovak"),
     *             @OA\Property(property="contrb_en", type="string", example="For development of a new method", description="Contribution in English")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prize updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Prize updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prize not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Prize not found")
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
     * @param int $id The prize ID to update
     * @param string $rok Year of the prize
     * @param string $category Category of the prize
     * @param string $contirb_sk Contribution description in Slovak
     * @param string $contrb_en Contribution description in English
     * @return int|string ID of the updated prize or error message
     */
    public function update($id,$rok, $category, $contirb_sk, $contrb_en )
    {
        $stmt = $this->db->prepare("UPDATE prize SET rok = ?, category = ?, contirb_sk = ?, contrb_en = ?  WHERE id = ?");
        $stmt->bind_param('ssssi', $rok, $category, $contirb_sk, $contrb_en,$id );
        try {
            $stmt->execute();
            return $this->db->insert_id;

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

    }
    /**
     * Find prizes associated with a person.
     *
     * @OA\Get(
     *     path="/prize/person/{id}",
     *     tags={"prize", "person"},
     *     summary="Find prizes by person ID",
     *     description="Returns prizes associated with a specific person",
     *     operationId="findById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the person to find prizes for",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="person_id", type="integer", example=1),
     *                 @OA\Property(property="prize_id", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No prizes found for person",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No prizes found for this person")
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
     * @return array Prizes associated with the person
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM person_prize WHERE person_id = ?");
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
     * Update the prize assigned to a person.
     *
     * @OA\Put(
     *     path="/prize/person/update",
     *     tags={"prize", "person"},
     *     summary="Update the prize for a person",
     *     description="Updates the prize association for a specified person",
     *     operationId="updatePersonPrize",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Association data to update",
     *         @OA\JsonContent(
     *             required={"person_id", "prize_id"},
     *             @OA\Property(
     *                 property="person_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the person"
     *             ),
     *             @OA\Property(
     *                 property="prize_id",
     *                 type="integer",
     *                 example=2,
     *                 description="New prize ID to associate with the person"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prize association updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="affected_rows", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="Prize association updated successfully")
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
     * @param int $newId The new prize ID
     * @return int Number of affected rows
     */
public function updatePersonPrize($id, $newId)
{
    $stmt = $this->db->prepare("UPDATE person_prize SET prize_id = ? WHERE person_id = ?");
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
     * Find detail information by prize ID.
     *
     * @OA\Get(
     *     path="/prize/{id}/details",
     *     tags={"prize"},
     *     summary="Find details by prize ID",
     *     description="Returns the details_id associated with a prize",
     *     operationId="findDetailByPrizeId",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the prize to get details for",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="details_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prize not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Prize not found")
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
     * @param int $id The prize ID
     * @return array Details ID for the prize
     */
    public function findDetailByPrizeId($id)
    {
        // This method is not used in the current context
        $stmt = $this->db->prepare("SELECT details_id FROM prize WHERE id = ?");
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
     * Create a new prize.
     *
     * @OA\Post(
     *     path="/prize",
     *     tags={"prize"},
     *     summary="Create a new prize",
     *     description="Adds a new Nobel prize to the database",
     *     operationId="store",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Prize data to add",
     *         @OA\JsonContent(
     *             required={"rok", "category", "contirb_sk"},
     *             @OA\Property(property="rok", type="string", example="2023", description="Year of the prize"),
     *             @OA\Property(property="category", type="string", example="Literature", description="Prize category"),
     *             @OA\Property(property="contirb_sk", type="string", example="Za výnimočný prínos", description="Contribution in Slovak"),
     *             @OA\Property(property="contrb_en", type="string", example="For exceptional contribution", description="Contribution in English"),
     *             @OA\Property(property="details_id", type="integer", example=7, description="ID of associated details (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Prize created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=5),
     *             @OA\Property(property="message", type="string", example="Prize created successfully")
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
     * @param string $rok Year of the prize
     * @param string $category Category of the prize
     * @param string $contirb_sk Contribution description in Slovak
     * @param string $contrb_en Contribution description in English (optional)
     * @param int $details_id ID of associated details (optional)
     * @return int|string ID of the newly created prize or error message
     */
    public function store( $rok, $category, $contirb_sk, $contrb_en = null ,$details_id = null)
    {
        $stmt = $this->db->prepare("INSERT INTO prize(rok,category,contirb_sk,contrb_en,details_id)VALUES (?,?,?,?,?)");
        $stmt->bind_param('ssssi', $rok, $category, $contirb_sk, $contrb_en ,$details_id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
        return $this->db->insert_id;

    }

    /**
     * Delete a prize by ID.
     *
     * @OA\Delete(
     *     path="/prize/{id}",
     *     tags={"prize"},
     *     summary="Delete a prize",
     *     description="Deletes a Nobel prize from the database",
     *     operationId="destroy",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the prize to delete",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prize deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Prize deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Prize not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Prize not found")
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
     * @param int $id The ID of the prize to delete
     * @return int Result code (0 for success)
     */
    public function destroy($id)
    {
        $stmt = $this->db->prepare("DELETE  FROM prize WHERE id = ?");
        $stmt->bind_param('i', $id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
        return 0;
    }
}