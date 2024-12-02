<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('SearchProducts', template: 'components/SearchProducts.html.twig')]
class SearchProducts
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?string $query = null;
}
