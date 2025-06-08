<?php

/**
 * @OA\Info(
 *     title="My First API",
 *     version="0.1"
 * )
 */

/**
 * @OA\Schema(
 *     schema="detail",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="language_sk", type="string"),
 *         @OA\Property(property="language_eng", type="string"),
 *         @OA\Property(property="genre_sk", type="string"),
 *         @OA\Property(property="genre_eng", type="string")
 *     }
 * )
 */


class Detail
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @OA\Get(
     *     path="/details",
     *     tags={"details"},
     *     summary="Get all details",
     *     operationId="getAllDetails",
     *     @OA\Response(
     *         response=200,
     *         description="List of details",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/detail")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM prize_details");
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
     * Get a detail.
     *
     * @OA\Get(
     *     tags={"details"},
     *     path="/details/{id}",
     *     operationId="detailShow",
     *     @OA\PathParameter(
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Header(
     *             header="X-Rate-Limit",
     *             description="calls per hour allowed by the user",
     *             @OA\Schema(
     *                 type="integer",
     *                 format="int32"
     *             )
     *         ),
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/detail"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @param ?int $id the detail id
     */
    public function show($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM prize_details WHERE id = ?");
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
     * Add a product.
     *
     * @OA\Post(
     *     path="/details",
     *     tags={"details"},
     *     summary="Add details",
     *     operationId="detailStore",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/detail")
     *     ),
     *     @OA\RequestBody(
     *         description="New detail",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/detail")
     *             )
     *         )
     *     )
     * )
     */
    public function update($id,$language_sk,$genre_sk, $language_eng = null, $genre_eng = null)
    {
        $stmt = $this->db->prepare("UPDATE prize_details SET language_sk = ?,language_eng = ?,genre_sk = ?,genre_eng = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $language_sk, $language_eng, $genre_sk, $genre_eng,$id);

        try {
            $stmt->execute();
            return $this->db->insert_id;

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }

    }



    /**
     * Update an existing details.
     *
     * @OA\Put(
     *     path="/details",
     *     tags={"details"},
     *     operationId="detailUpdate",
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found"
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception"
     *     ),
     *     security={
     *         {"petstore_auth": {"write:details", "read:details"}}
     *     },
     * )
     */
    public function store($language_sk, $language_eng, $genre_sk, $genre_eng)
    {
        $stmt = $this->db->prepare("INSERT INTO prize_details(language_sk, language_eng, genre_sk, genre_eng) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $language_sk, $language_eng, $genre_sk, $genre_eng);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
        return $this->db->insert_id;
    }

    /**
     * @OA\Delete(
     *     path="/details/{id}",
     *     tags={"details"},
     *     summary="Delete details",
     *     description="This can only be done by the logged in user.",
     *     operationId="detailDestroy",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the detail to delete",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="prize successfully deleted",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Detail not found",
     *     )
     * )
     */
    public function destroy($id)
    {
        $stmt = $this->db->prepare("DELETE  FROM prize_details WHERE id = ?");
        $stmt->bind_param('i', $id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
        return 0;
    }

}