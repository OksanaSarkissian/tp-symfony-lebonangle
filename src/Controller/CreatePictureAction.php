<?php
// api/src/Controller/CreatePictureAction.php

namespace App\Controller;

use App\Entity\Picture;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class CreatePictureAction extends AbstractController
{
    public function __invoke(Request $request): Picture
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $Picture = new Picture();
        $Picture->setImageFile($uploadedFile);

        return $Picture;
    }
}