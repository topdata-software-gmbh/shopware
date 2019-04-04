<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Context;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Content\Rule\RuleCollection;

class RuleLoaderResult
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var RuleCollection
     */
    protected $matchingRules;

    public function __construct(Cart $cart, RuleCollection $rules)
    {
        $this->cart = $cart;
        $this->matchingRules = $rules;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getMatchingRules(): RuleCollection
    {
        return $this->matchingRules;
    }
}
