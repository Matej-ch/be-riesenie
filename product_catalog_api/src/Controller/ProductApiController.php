<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductApiController extends AbstractController
{
    public function test(IriConverterInterface $iriConverter)
    {
        $iriConverter->getIriFromResource('');
    }
}