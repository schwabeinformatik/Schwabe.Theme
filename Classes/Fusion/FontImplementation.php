<?php
namespace Schwabe\Theme\Fusion;

/*
 * This file is part of the Schwabe.Theme package.
 *
 * (c) 2017, Alexander Kappler
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Schwabe\Theme\Domain\Model\Font;
use Schwabe\Theme\Domain\Repository\SettingsRepository;
use Schwabe\Theme\Service\Build;
use Schwabe\Theme\Service\Compile;
use Schwabe\Theme\Service\Request;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Utility\Arrays;

class FontImplementation extends AbstractFusionObject
{
    const GOOGLE_WEBFONT_API = '//fonts.googleapis.com/css';

    /**
     * @Flow\InjectConfiguration(package="Schwabe.Theme")
     * @var array
     */
    protected $configuration;

    /**
     * @Flow\Inject
     * @var Request
     */
    protected $requestService;

    /**
     * @Flow\Inject
     * @var SettingsRepository
     */
    protected $settingsRepository;

    /**
     * @Flow\Inject
     * @var Build
     */
    protected $buildService;

    /**
     * @Flow\Inject
     * @var Compile
     */
    protected $compileService;

    /**
     * Render link-tag for cdn / google fonts
     *
     * @return mixed
     */
    public function evaluate()
    {
        $currentSitePackageKey = $this->requestService->getCurrentSitePackageKey();
        $customSettings = $this->settingsRepository->findByIdentifier($currentSitePackageKey)->getCustomSettings();

        if (!isset($customSettings['font']) || !isset($customSettings['font']['type']) || !isset($customSettings['font']['type']['font'])) {
            return null;
        }

        if ($this->configuration === null) {
            return null;
        }

        $presetVariables = $this->configuration['scss']['presetVariables'];

        if (isset($presetVariables['font']['type']['font']) && is_array($presetVariables['font']['type']['font']) && count($presetVariables['font']['type']['font']) > 0) {
            $fontSettings = $presetVariables['font']['type']['font'];
        } else {
            return null;
        }

        $fontSettings = Arrays::arrayMergeRecursiveOverrule($fontSettings, $customSettings['font']['type']['font']);
        $fonts = $this->buildService->buildFontOptions($currentSitePackageKey);

        if (!isset($fonts) || !is_array($fonts) || count($fonts) === 0) {
            return null;
        }

        $externalFonts = [];

        foreach ($fontSettings as $fontSetting) {
            if (!isset($fontSetting['value']['family'])) {
                continue;
            }

            $font = $this->compileService->findFontByName($fontSetting['value']['family'], $fonts);
            if (!isset($font) || $font->getFontSource() === Font::FONT_SOURCE_SYSTEM || $font->getFontSource() === Font::FONT_SOURCE_LOCAL) {
                continue;
            }

            // Check if at least one font variant is available
            $variantsArray = [];
            if (isset($fontSetting['value']['variants'])) {
                if (is_array($fontSetting['value']['variants'])) {
                    $variantsArray = $fontSetting['value']['variants'];
                } elseif (is_string($fontSetting['value']['variants'])) {
                    // check: why would this ever be a string?
                    $variantsArray = json_decode($fontSetting['value']['variants']);
                }
            }

            if ($variantsArray !== []) {
                switch ($font) {
                    case ($font->getFontSource() === Font::FONT_SOURCE_GOOGLE):
                        $externalFonts['google'][$fontSetting['value']['family']]['settings'] = $fontSetting;
                    break;

                    case ($font->getFontSource() === Font::FONT_SOURCE_CDN):
                        $externalFonts['cdn'][$fontSetting['value']['family']]['font'] = $font;
                        $externalFonts['cdn'][$fontSetting['value']['family']]['settings'] = $fontSetting;
                    break;

                    default:
                }
            }
        }

        $html = $this->getFontLinkTag($externalFonts);

        return $html;
    }

    /**
     * Return the <link> tag for the given font
     *
     * @param array $externalFonts
     * @return string
     */
    private function getFontLinkTag(array $externalFonts)
    {
        $link = '';

        if (isset($externalFonts['cdn']) && count($externalFonts['cdn']) > 0) {
            foreach ($externalFonts['cdn'] as $cdnFontLink) {
                $link .= $this->cdnLinkTag($cdnFontLink['font'], ['regular']);
            }
        }

        if (isset($externalFonts['google']) && count($externalFonts['google']) > 0) {
            $link .= $this->googleWebfontLinkTag($externalFonts['google']);
        }

        return $link;
    }

    /**
     * Build cdn webfont <link> tag
     *
     * @param Font $font The font which should be added
     * @param array $variants cdn Font link can only contain one href url (cdn path)
     * @return string
     */
    private function cdnLinkTag(Font $font, array $variants)
    {
        $link = '<link rel="stylesheet" href="';
        foreach ($font->getFiles() as $fileKey => $file) {
            if ($fileKey === $variants[0]) {
                $link .= $file;
            }
        }
        $link .= '">';

        return $link;
    }

    /**
     * Build google webfont specific <link> tag
     *
     * @param array $googleFonts Array of google fonts which should be combined to one link tag
     * @return string
     */
    private function googleWebfontLinkTag(array $googleFonts)
    {
        $link = '<link rel="stylesheet" href="';
        $link .= self::GOOGLE_WEBFONT_API;
        $link .= '?family=';

        $n = 0;
        foreach ($googleFonts as $googleFont) {
            if ($n > 0) {
                $link .= '|';
            }

            $link .= str_replace(' ', '+', $googleFont['settings']['value']['family']);

            $variants = json_decode($googleFont['settings']['value']['variants']);

            if (isset($variants) && count($variants) > 0) {
                $link .= ':' . implode(",", $variants);
            }

            $n++;
        }

        $link .= '">';

        return $link;
    }
}
