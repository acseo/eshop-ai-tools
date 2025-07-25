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
        $firstPrompt = <<<'EOF'
        Tu es un expert dans l'analyse d'images pour un site e-commerce. Ton métier consiste à regarder une image et en déduire des mots clés.
        Les mots clés peuvent correspondre à un produit, une couleur, une indication géographique (ville, région) ou toute information utile permettant de classifier des produits sur un sie e-commerce.
    
        Voici la liste des mots clés déjà existants sur le site : __KEYWORDS__.
    
        Tu dois générer 10 mots clés.
        Respecte les consignes suivantes :
        - Tu peux utiliser ces mots clés ou en créer de nouveaux.
        - Le mot clé n'est constitué que de 1 ou 2 mots.
        - Les mots clés sont dans la langue : __LOCALE__
        EOF;
        $messages = [];

        $prompt = str_replace(
            ['__KEYWORDS__', '__LOCALE__'],
            [implode(', ',$keywords), $locale],
            $firstPrompt
        );

        $messages[] = ['role' => 'assistant', 'content' => $prompt];

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
        $firstPrompt = <<<'EOF'
         Tu es un expert dans la rédaction de texte  pour un site e-commerce. Ton métier consiste à lire un texte, et en déduire des mots clés.
        Les mots clés peuvent correspondre à un produit, une couleur, une indication géographique (ville, région), ou toute information utile permettant de classifier des produits sur un sie e-commerce.
    
        Voici la liste des mots clés déjà existants sur le site : __KEYWORDS__.
    
        Tu dois générer 10 mots clés.
        Respecte les consignes suivantes :
        - Tu peux utiliser ces mots clés ou en créer de nouveaux.
        - Le mot clé n'est constitué que de 1 ou 2 mots.
        - Les mots clés sont en lien avec le texte.
        - Les mots clés sont dans la langue : __LOCALE__

        Le texte est le suivant : __TEXT__
        EOF;

        $messages = [];

        $prompt = str_replace(
            ['__LOCALE__', '__KEYWORDS__', '__TEXT__'],
            [$locale, implode(',', $keywords), $text],
            $firstPrompt
        );

        $messages[] = ['role' => 'assistant', 'content' => $prompt];

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

        $content = $result->choices[0]->message->content;
        return json_decode($content, true);
    }      

}
