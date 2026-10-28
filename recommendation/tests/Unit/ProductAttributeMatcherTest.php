<?php

namespace Tests\Unit;

use App\Support\ProductAttributeMatcher;
use Tests\TestCase;

class ProductAttributeMatcherTest extends TestCase
{
    public function test_empty_required_filters_match_any_product(): void
    {
        $this->assertTrue(ProductAttributeMatcher::matches(['color' => 'pink'], []));
    }

    public function test_requires_matching_attribute_values(): void
    {
        $this->assertTrue(ProductAttributeMatcher::matches(
            ['color' => 'green', 'material' => 'linen'],
            ['color' => 'green'],
        ));

        $this->assertFalse(ProductAttributeMatcher::matches(
            ['color' => 'blue'],
            ['color' => 'green'],
        ));
    }
}
