<?php

namespace ACSEO\EshopAiTools\Service;

use OpenAI\Client;

class PictureToProduct
{
    public function __construct(private Client $client, private string $model = 'gpt-4o-mini')
    {
        
    }

    public function fromPictures(array $pictures, string $locale = 'en_US')
    {
        $systemPrompt = <<<'EOF'
        # Identity
        
        Tu es un expert dans l'analyse d'images pour un site e-commerce. Ton métier consiste à regarder une image et générer le nom de un ou plusieurs produits.
        La photo contient soit des visuels de produits, soit une liste texte de produits.
        
        # Instructions

        * Détermine la quantité des produits.
        * Le nom des produits doit être généré en lien avec l'image, par exemple par rapport aux mots que tu trouves sur celle-ci.
        * Texte en lien avec l'image.
        * Génère tous les noms des produits que tu identifies sur l'image.
        * Le texte généré est dans la langue : __LOCALE__.
        EOF;

        $systemPrompt = str_replace(
            ['__LOCALE__', ],
            [$locale],
            $systemPrompt
        );

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];
        foreach($pictures as $picture) {
            $type = pathinfo($picture, PATHINFO_EXTENSION);
            $data = file_get_contents($picture);
            $dataUri = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
            $messages[] = [
                'role' => 'user',
                'content' => [
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => $dataUri
                        ]
                    ]
                ]
            ];
        }

        return $this->execute($messages);
    }

    private function execute(array $messages)
    {
        $result = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'content',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'content' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'brand' => ['type' => 'string'],
                                        'type' => ['type' => 'string'],
                                        'keywords' => ['type' => 'string'],
                                        'quantity' => ['type' => 'integer'],
                                    ],
                                    'required' => ['title', 'brand', 'type', 'keywords', 'quantity'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'additionalProperties' => false,
                        'required' => ['content'],
                    ],
                    'strict' => true,
                ],
            ],
        ]);

        return json_decode($result->choices[0]->message->content, true);
    }
}
