<?php

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Services\ItemsService;
use Directus\Services\FilesServices;

return [
    // '' means it is located at: `/custom/<endpoint-id>`
    // '/` means it is located at: `/custom/<endpoint-id>/`
    // 'test' and `/test` means it is located at: `/custom/<endpoint-id>/test
    // if the handler is a Closure or Anonymous function, it's binded to the app container. Which means $this = to the app container.
    '' => [
        'method' => 'GET',
        'handler' => function (Request $request, Response $response) {

            // Get all answers from DB
            $itemsService = new ItemsService($this);
            $filesServices = new FilesServices($this);

            $categories = $itemsService->findAll('categories')["data"];
            for ($i = 0; $i < count($categories); $i++) {
                $params = [
                    'filter' => [
                        'category' => [
                            'eq' => $categories[$i]["id"],
                        ],
                    ],
                ];

                $products = $itemsService->findAll('products', $params);
                $randomProduct = $products["data"][array_rand($products["data"])];

                $imageParams = [
                    'filter' => [
                        'id' => [
                            'eq' => $randomProduct["image"],
                        ],
                    ],
                ];

                $imageFile = $filesServices->findAll($imageParams);

                if ($imageFile["data"]) $categories[$i]["image"] = $imageFile["data"][0];

                $translations = $itemsService->findAll('category_translations', $params);

                $categories[$i]["translations"] = $translations["data"];
            }

            return $response->withJson([
                'data' => $categories,
            ]);
        }
    ]
];
