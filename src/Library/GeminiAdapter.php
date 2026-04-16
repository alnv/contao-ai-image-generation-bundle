<?php

namespace Alnv\ContaoAiImageGenerationBundle\Library;

use Contao\File;
use Contao\System;
use Gemini;
use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Data\ImageConfig;
use Gemini\Enums\MimeType;

// https://github.com/google-gemini-php/client
// https://ai.google.dev/gemini-api/docs/image-generation?hl=de
class GeminiAdapter
{

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function generate(string $prompt, string $destFilename, array $referencesImages = [], array $options = []): void
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        $client = Gemini::client($this->apiKey);
        $model = $options['model'] ?? 'gemini-3.1-flash-image-preview';
        $references = $this->setReferences($referencesImages);
        $parts = [...[$prompt], ...$references];

        $imageConfig = new ImageConfig(aspectRatio: $options['aspectRatio'] ?? '1:1');
        $generationConfig = new GenerationConfig(imageConfig: $imageConfig);

        $response = $client->generativeModel(model: $model)
            ->withGenerationConfig($generationConfig)
            ->generateContent($parts);

        $blob = $response->candidates[0]->content->parts[0]->inlineData;
        $base64Data = $blob->data;

        \file_put_contents($rootDir . '/' . $destFilename, \base64_decode($base64Data));
    }

    protected function setReferences(array $images): array
    {
        $references = [];

        foreach ($images as $image) {
            $file = new File($image);
            $mimeType = null;

            switch ($file->mime) {
                case 'image/jpeg':
                    $mimeType = MimeType::IMAGE_JPEG;
                    break;
                case 'image/png':
                    $mimeType = MimeType::IMAGE_PNG;
                    break;
                case 'image/webp':
                    $mimeType = MimeType::IMAGE_WEBP;
                    break;
            }
            $references[] = new Blob(mimeType: $mimeType, data: $this->getBase64Data($image));
        }

        return $references;
    }

    protected function getBase64Data(string $path): string
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        return \base64_encode(\file_get_contents($rootDir . '/' . $path));
    }
}