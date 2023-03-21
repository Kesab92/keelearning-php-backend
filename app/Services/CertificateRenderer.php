<?php

namespace App\Services;

use App\Models\App;
use App\Models\User;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class CertificateRenderer
{
    abstract public function getCertificate();

    abstract public function getApp() : App;

    abstract public function getUser() : User;

    abstract public function getTitle() : string;

    abstract public function getReplacementValues() : array;

    /**
     * Renders and returns the certificate PDF
     *
     * @param string|null $filename Filename for download, without extension
     * @return StreamedResponse
     */
    public function render(?string $filename = null): StreamedResponse
    {
        $certificateTemplate = $this->getCertificate();
        if (! $certificateTemplate) {
            echo 'Es wurde noch kein Template angelegt.';
            exit;
        }
        $size = $certificateTemplate->background_image_size;
        if (! $size) {
            echo 'Es wurde noch kein Bild hinterlegt.';
            exit;
        }

        $inchToMM = 25.4;
        $ptToPx = 1.33;
        $sizeFactor = 1;

        $size = json_decode($size, true);
        // Calculate the size factor
        // The certificate editor has a maximum size of 1024x768px
        // When the user uploads an image larger than that, we want to preserve the original resolution
        // We do this by scaling the pdf to the same size like the editor, but increasing the dpi of the pdf
        if (isset($size['actualHeight'])) {
            $sizeFactor = $size['actualHeight'] / $size['height'];
        }
        $averageScreenDpi = 72; // there is no way for us to know the actual DPI of the used screen
        $screenSizeModificator = 2; // about how much bigger the "physical" PDF should be compared to the screen

        // scale the document to be around double as big in mm as the preview display on screen
        // that might be small, but we increase the dpi accordingly so it evens out
        $mmSize['height'] = $inchToMM * $size['height'] / $averageScreenDpi * $screenSizeModificator;
        $mmSize['width'] = $inchToMM * $size['width'] / $averageScreenDpi * $screenSizeModificator;
        $dpi = round($averageScreenDpi * $sizeFactor / $screenSizeModificator);

        $elements = $certificateTemplate->elements;
        if ($elements) {
            $elements = json_decode($elements, true);
            foreach ($elements as &$element) {
                foreach (['left', 'width'] as $param) {
                    $element[$param] = $element[$param] / $size['width'] * 100;
                }
                foreach (['top', 'height'] as $param) {
                    $element[$param] = $element[$param] / $size['height'] * 100;
                }
                $element['text'] = preg_replace_callback(
                    "|font-size: (\d+)pt|",
                    function ($match) use ($averageScreenDpi, $inchToMM, $ptToPx, $screenSizeModificator) {
                        return 'font-size: '.(($match[1] * $ptToPx) / $averageScreenDpi * $screenSizeModificator * $inchToMM).'mm';
                    },
                    $element['text']
                );
                $element['text'] = $this->fillPlaceholders($element['text']);
            }
        } else {
            $elements = [];
        }
        $html = view('certificates.certificate')->with([
            'baseFontSize' => ((10 * $ptToPx) / $averageScreenDpi * $screenSizeModificator * $inchToMM), // 10pt is standard in our editor
            'template' => $certificateTemplate,
            'size' => $mmSize,
            'elements' => $elements,
        ])->render();

        $snappy = new Pdf(realpath(public_path().'/../bin/wkhtmltopdf'));
        $snappy->setOptions([
            'dpi' => $dpi,
            'image-dpi' => $dpi,
            'image-quality' => 90,
            'page-height' => $mmSize['height'],
            'page-width' => $mmSize['width'],
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            'title' => $this->getTitle(),
            'disable-javascript' => true,
            'encoding' => 'utf8',
            'no-outline' => true,
            'disable-smart-shrinking' => true,
        ]);
        $headers = [
            'Content-Type' => 'application/pdf',
        ];

        if ($filename) {
            $headers['Content-Disposition'] = 'inline; filename="' . str_replace('"', '', $filename) . '.pdf"';
        }

        return response()->stream(function () use ($snappy, $html) {
            echo $snappy->getOutputFromHtml($html);
        }, 200, $headers);
    }

    /**
     * Replaces the placeholders in the $text with actual values.
     *
     * @param $text
     * @return mixed
     */
    private function fillPlaceholders($text)
    {
        $replace = $this->getReplacementValues();
        foreach (array_keys($this->getApp()->getUserMetaDataFields(true)) as $appMetaKey) {
            $replace['meta_'.$appMetaKey] = '';
        }

        foreach ($this->getUser()->metafields as $metafield) {
            $replace['meta_'.$metafield['key']] = $metafield['value'];
        }

        return str_replace(
            array_map(function ($v) {
                return '%'.$v.'%';
            }, array_keys($replace)),
            array_values($replace),
            $text
        );
    }
}
