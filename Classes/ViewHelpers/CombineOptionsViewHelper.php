<?php

namespace Schwabe\Theme\ViewHelpers;

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

class CombineOptionsViewHelper extends AbstractViewHelper
{
    public function render(array $options, string $category)
    {
        $categories = explode(',', $category);
        $result = [];

        foreach ($categories as $c) {
            if (array_key_exists($c, $options)) {
                $result = array_merge($result, $options[$c]);
            }
        }

        sort($result);

        return $result;
    }
}
