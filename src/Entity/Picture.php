<?php

namespace App\Entity;

use App\Controller\CreatePictureAction;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Controller\PictureController;
use App\Repository\PictureRepository;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


#[ORM\Entity(repositoryClass: PictureRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'picture:item']),
        new GetCollection(normalizationContext: ['groups' => 'picture:list']),
        //new Post(denormalizationContext: ['groups'=> 'postpicture:item']),
 
        new Post(
            controller: PictureController::class,
            deserialize: false,
            validationContext: ['groups' => ['Default', 'app_picture_new']],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'imageFile' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ],
    order: ['id' => 'ASC'],
    paginationEnabled: true,
)]
#[Vich\Uploadable]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    
    #[Groups(['picture:list', 'picture:item', 'postpicture:item'])]
    private ?string $path = null;

    #[Vich\UploadableField(mapping: 'imageFile', fileNameProperty: 'imageFile', size: 'imageSize')]
    private ?File $imageFile = null;


    #[ORM\Column]
    
    #[Groups(['picture:list', 'picture:item', 'postpicture:item'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'pictures')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Advert $advert = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    public function setAdvert(?Advert $advert): static
    {
        $this->advert = $advert;

        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile) 
        { 
            $this->updatedAt = new \DateTimeImmutable();  
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

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
