<?php

namespace Alnv\ContaoAiImageGenerationBundle\Library;

use Contao\Environment;
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

        $candidate = $response->candidates[0];
        $imageData = null;
        foreach ($candidate->content->parts as $part) {
            if (isset($part->inlineData)) {
                $imageData = $part->inlineData->data;
                break;
            }
        }

        if (!$imageData) {
            $errorMsg = $candidate->content->parts[0]->text ?? 'Unbekannter Fehler (Keine Bilddaten im Response)';
            throw new \Exception('Gemini API Error: ' . $errorMsg);
        }

        $decodedData = \base64_decode($imageData, true);
        if (!$decodedData) {
            throw new \Exception('Die empfangenen Bilddaten sind kein gültiges Base64.');
        }

        \file_put_contents($rootDir . '/' . $destFilename, $decodedData);
    }

    public function getPayload(string $prompt, string $destFilename, array $referencesImages = [], array $options = []): array
    {
        $model = $options['model'] ?? 'gemini-3.1-flash-image-preview';

        return [
            'prompt' => $prompt,
            'webhookUrl' => $options['webhookUrl'] ?? '',
            'references' => $this->setReferences($referencesImages, false),
            'config' => [
                'model' => $model,
                'aspectRatio' => $options['aspectRatio'] ?? '1:1'
            ]
        ];
    }

    protected function setReferences(array $images, $blnUseBlob = true): array
    {
        $references = [];
        $container = System::getContainer();
        $imageFactory = $container->get('contao.image.picture_factory');
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        foreach ($images as $image) {
            $item = $imageFactory->create($rootDir . '/' . $image, [800, 800]);
            $imageContent = \file_get_contents(Environment::get('url') . '/' . $item->getImg()['src']->getUrl($rootDir), false, \stream_context_create([
                "ssl" => [
                    "verify_peer" => false
                ]
            ]));

            $file = new File($item->getImg()['src']->getUrl($rootDir));
            $mimeType = match ($file->mime) {
                'image/jpeg' => MimeType::IMAGE_JPEG,
                'image/png' => MimeType::IMAGE_PNG,
                'image/webp' => MimeType::IMAGE_WEBP,
                default => null,
            };

            if (!$mimeType) {
                continue;
            }

            $base64Data = $this->getBase64Data($imageContent);
            $references[] = $blnUseBlob
                ? new Blob(mimeType: $mimeType, data: $base64Data)
                : ['mimeType' => $mimeType, 'data' => $base64Data];
        }

        return $references;
    }

    protected function getBase64Data(string $imageContent): string
    {
        return \base64_encode($imageContent);
    }
}