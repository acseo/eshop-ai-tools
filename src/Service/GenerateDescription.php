<?php
namespace ACSEO\EshopAiTools\Service;

use OpenAI\Client;

class GenerateDescription
{
    public function __construct(private Client $client, private string $model = 'gpt-4o-mini')
    {
        
    }

    public function fromPictures(array $pictures, string $locale = 'en_US', array $keywords = [])
    {
        $systemPrompt = <<<'EOF'
        # Identity
        
        Tu es un expert dans l'analyse d'images pour un site e-commerce. Ton métier consiste à regarder une image et générer un titre, une description courte, une description longue, la méta mot clé, et la meta description.
        Les contenus générés doivent être en lien avec l'image, et favoriser le SEO du site.
         
        # Instructions
        
        * Tu peux utiliser des mots clés déjà existants si proposé ou en créer de nouveaux.
        * Texte en lien avec l'image.
        * Texte respectant les standards SEO, avec la bonne structure et nombre de caractères.
        * Les mots clés sont dans la langue : __LOCALE__.
        
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
        
        Tu es un expert dans la rédaction de texte pour un site e-commerce. Ton métier consiste à lire un texte, et générer un titre, une description courte, une description longue, la méta mot clé, et la meta description.
        Les contenus générés doivent être en lien avec le texte, et favoriser le SEO du site.
        
        # Instructions
        
        * Tu peux utiliser ces mots clés ou en créer de nouveaux.
        * Texte en lien avec le texte.
        * Texte respectant les standards SEO, avec la bonne structure et nombre de caractères.
        * Les mots clés sont dans la langue : __LOCALE__.
        
        # Context
        
        __KEYWORDS__
        EOF;

        $systemPrompt = str_replace(
            ['__LOCALE__', '__KEYWORDS__'],
            [
                $locale,
                [] !== $keywords ? sprintf('Voici la liste des mots clés déjà existants sur le site : %s.', implode(',', $keywords)) : '',
            ],
            $systemPrompt
        );

        return $this->execute([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => sprintf('Voici le text à décrire: %s', $text)]
        ]);
    }
    
    private function execute(array $messages)
    {
        $result = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
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
                                        'short_description' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'meta_description' => ['type' => 'string'],
                                        'meta_keywords' => ['type' => 'string'],
                                    ],
                                    'required' => ['title', 'short_description', 'description', 'meta_description', 'meta_keywords'],
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

        $content = $result->choices[0]->message->content;
        return json_decode($content, true);
    }
}
