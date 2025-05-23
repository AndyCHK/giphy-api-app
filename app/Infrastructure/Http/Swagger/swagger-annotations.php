<?php

/**
 * @OA\Info(
 *     title="API Giphy",
 *     version="1.0.0",
 *     description="API para interactuar con Giphy y gestionar favoritos",
 *     @OA\Contact(
 *         email="contacto@ejemplo.com",
 *         name="Equipo de API"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *     schema="GifDTO",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="xT9IgDEI1iZyb2wqo8"),
 *     @OA\Property(property="title", type="string", example="Happy dancing cat"),
 *     @OA\Property(property="url", type="string", example="https://giphy.com/gifs/cat-dancing-happy"),
 *     @OA\Property(property="images", type="object")
 * )
 */