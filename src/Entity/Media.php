<?php


namespace WebEtDesign\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity
 * @ORM\Table(name="wd_media__media")
 *
 * @Vich\Uploadable()
 * @ORM\EntityListeners({"WebEtDesign\MediaBundle\Listener\MediaListener"})
 */
class Media
{

    use TimestampableEntity;

    /**
     * @var int|null $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected ?int $id = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private string $label;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string")
     */
    private ?string $category = null;

    private ?string $categoryLabel = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    private ?string $fileName = null;

    /**
     * @Vich\UploadableField(mapping="wd_media", fileNameProperty="fileName")
     * @var File|null
     */
    private ?File $file = null;


    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private ?string $mimeType = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private ?string $extension = null;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $cropData = null;

    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Media
     */
    public function setLabel(string $label): Media
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string|null $category
     * @return Media
     */
    public function setCategory(?string $category): Media
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return Media
     */
    public function setFileName(?string $fileName): Media
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @return Media
     */
    public function setFile(?File $file): Media
    {
        $this->file = $file;

        if ($file) {
            $this->setUpdatedAt(new \DateTime());
            $this->setCropData(null);
        }

        return $this;
    }

    /**
     * @param string|null $mimeType
     * @return Media
     */
    public function setMimeType(?string $mimeType): Media
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $categoryLabel
     * @return Media
     */
    public function setCategoryLabel(?string $categoryLabel): Media
    {
        $this->categoryLabel = $categoryLabel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategoryLabel(): ?string
    {
        return $this->categoryLabel;
    }

    /**
     * @param string|null $extension
     * @return Media
     */
    public function setExtension(?string $extension): Media
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return string|null
     */
    public function getCropData(): ?string
    {
        return $this->cropData;
    }

    /**
     * @return array|null
     */
    public function getCropDataForFormatDevice($format, $device): ?array
    {
        $crop = json_decode($this->getCropData() ? $this->getCropData() : [], true);

        return isset($crop[$format]) ? $crop[$format][$device] ?? null : null;
    }



    /**
     * @param string|null $cropData
     * @return Media
     */
    public function setCropData(?string $cropData): Media
    {
        $this->cropData = $cropData;
        return $this;
    }
}
