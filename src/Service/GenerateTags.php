<?php

namespace ACSEO\EshopAiTools\Service;

use OpenAI\Client;

class GenerateTags
{
    public function __construct(private Client $client, private string $model = 'gpt-4o-mini')
    {
        
    }

    public function fromPictures(array $pictures, string $locale = 'en_US', array $keywords = [])
    {
        $systemPrompt = <<<'EOF'
        # Identity
        
        Tu es un expert dans l'analyse d'images pour un site e-commerce. Ton métier consiste à regarder une image et en déduire des mots clés.
        Les mots clés peuvent correspondre à un produit, une couleur, une indication géographique (ville, région) ou toute information utile permettant de classifier des produits sur un sie e-commerce.
    
        # Instructions
        
        * Tu dois générer 10 mots clés.
        * Tu peux utiliser des mots clés déjà existants si proposé ou en créer de nouveaux.
        * Le mot clé n'est constitué que de 1 ou 2 mots.
        * Les mots clés sont dans la langue : __LOCALE__
        
        # Context
        
        __KEYWORDS__
        EOF;

        $systemPrompt = str_replace(
            ['__LOCALE__', '__KEYWORDS__'],
            [
                $locale,
                [] !== $keywords ? sprintf('Voici la liste des mots clés déjà existants sur le site : %s.', implode(',', $keywords)) : 'Aucun mot clé déjà existant.',
            ],
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

    public function fromText(string $text, string $locale = 'en_US', array $keywords = [])
    {
        $systemPrompt = <<<'EOF'
        # Identity
        
        Tu es un expert dans la rédaction de texte pour un site e-commerce. Ton métier consiste à lire un texte, et en déduire des mots clés.
        Les mots clés peuvent correspondre à un produit, une couleur, une indication géographique (ville, région), ou toute information utile permettant de classifier des produits sur un site e-commerce.
        
        # Instructions
        
        * Tu dois générer 10 mots clés.
        * Tu peux utiliser des mots clés déjà existants si proposé ou en créer de nouveaux.
        * Le mot clé n'est constitué que de 1 ou 2 mots.
        * Les mots clés sont en lien avec le texte.
        * Les mots clés sont dans la langue : __LOCALE__
        
        # Context
        
        __KEYWORDS__
        EOF;

        $systemPrompt = str_replace(
            ['__LOCALE__', '__KEYWORDS__'],
            [
                $locale,
                [] !== $keywords ? sprintf('Voici la liste des mots clés déjà existants sur le site : %s.', implode(', ', $keywords)) : 'Aucun mot clé déjà existant.',
            ],
            $systemPrompt
        );

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => sprintf('Voici le texte : %s', $text)]
        ];

        return $this->execute($messages);
    }

    private function execute(array $messages)
    {
        $result = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'tags',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'tags' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                    ],
                                    'required' => ['name'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'additionalProperties' => false,
                        'required' => ['tags'],
                    ],
                    'strict' => true,
                ],
            ],
        ]);

        return json_decode($result->choices[0]->message->content, true);
    }
}
